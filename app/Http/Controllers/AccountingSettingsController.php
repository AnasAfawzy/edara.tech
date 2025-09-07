<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Models\AccountingSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Repositories\AccountRepository;

class AccountingSettingsController extends Controller
{
    protected $AccountRepository;

    // تعريف الحقول المسموحة كـ constant
    const ALLOWED_SETTINGS = [
        'default_customer_account' => 'Default Customers Account',
        'default_supplier_account' => 'Default Suppliers Account',
        'default_bank_account' => 'Default Banks Account',
        'default_cash_vault_account' => 'Default Vaults Account',
        'default_warehouse_account' => 'Default Warehouses Account'
    ];

    public function __construct(AccountRepository $AccountRepository)
    {
        $this->AccountRepository = $AccountRepository;
    }

    /**
     * عرض صفحة إعدادات الحسابات
     */
    public function index()
    {
        try {
            // جلب الحسابات الرئيسية والفرعية فقط
            $accounts = $this->AccountRepository->getModel()
                ->where(function ($query) {
                    $query->where('slave', 0)
                        ->orWhere('has_sub', 1);
                })
                ->orderBy('code')
                ->get(['id', 'name', 'code', 'slave', 'has_sub']);

            Log::info('Accounting settings page loaded', [
                'accounts_count' => $accounts->count(),
                'user_id' => Auth::id()
            ]);

            return view('AccountingSettings.index', compact('accounts'));
        } catch (Exception $e) {
            Log::error('Error loading accounting settings: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', __('Error loading accounting settings'));
        }
    }

    /**
     * حفظ إعدادات الحسابات
     */
    public function store(Request $request)
    {
        try {
            // التحقق من صحة البيانات
            $validated = $this->validateSettings($request);

            Log::info('Saving accounting settings', [
                'settings' => $validated,
                'user_id' => Auth::id()
            ]);

            // بدء المعاملة لضمان تكامل البيانات
            DB::beginTransaction();

            // حفظ الإعدادات المُدخلة فقط
            $this->saveSettings($validated);

            // تأكيد المعاملة
            DB::commit();

            Log::info('Accounting settings saved successfully', [
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('success', __('Accounts settings updated successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            Log::warning('Validation error in accounting settings', [
                'errors' => $e->errors(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error saving accounting settings: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', __('Error saving settings: ') . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * التحقق من صحة الإعدادات
     */
    private function validateSettings(Request $request): array
    {
        $rules = [];

        // إنشاء قواعد التحقق ديناميكياً - جميع الحقول اختيارية
        foreach (array_keys(self::ALLOWED_SETTINGS) as $setting) {
            $rules[$setting] = 'nullable|exists:accounts,id';
        }

        return $request->validate($rules);
    }

    /**
     * حفظ الإعدادات في قاعدة البيانات
     */
    private function saveSettings(array $validated): void
    {
        foreach (self::ALLOWED_SETTINGS as $key => $description) {
            // التأكد من أن المفتاح مسموح
            if (!array_key_exists($key, $validated)) {
                continue;
            }

            $value = $validated[$key];

            // إذا كانت القيمة فارغة، احذف الإعداد
            if (empty($value)) {
                $this->removeSetting($key);
                continue;
            }

            // حفظ أو تحديث ID الحساب
            AccountingSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'updated_at' => now(),
                    'updated_by' => Auth::id()
                ]
            );

            // حفظ اسم ورمز الحساب للعرض
            $this->saveAccountNameAndCode($key, $value);

            Log::info('Setting saved', [
                'key' => $key,
                'account_id' => $value,
                'user_id' => Auth::id()
            ]);
        }
    }

    /**
     * حذف إعداد معين
     */
    private function removeSetting(string $key): void
    {
        try {
            // حذف الإعداد الرئيسي
            AccountingSetting::where('key', $key)->delete();

            // حذف اسم الحساب المرتبط
            AccountingSetting::where('key', $key . '_name')->delete();

            Log::info('Setting removed', [
                'key' => $key,
                'user_id' => Auth::id()
            ]);
        } catch (Exception $e) {
            Log::error('Error removing setting: ' . $e->getMessage(), [
                'key' => $key,
                'user_id' => Auth::id()
            ]);
        }
    }

    /**
     * حفظ اسم ورمز الحساب
     */
    private function saveAccountNameAndCode(string $key, ?string $accountId): void
    {
        $nameKey = $key . '_name';
        $nameValue = '';

        if ($accountId) {
            $account = $this->AccountRepository->find($accountId);
            if ($account) {
                $nameValue = $account->name . ' — ' . $account->code;
            }
        }

        if (empty($nameValue)) {
            // إذا لم يتم العثور على الحساب، احذف الإعداد
            AccountingSetting::where('key', $nameKey)->delete();
        } else {
            // حفظ اسم الحساب
            AccountingSetting::updateOrCreate(
                ['key' => $nameKey],
                [
                    'value' => $nameValue,
                    'updated_at' => now(),
                    'updated_by' => Auth::id()
                ]
            );
        }
    }

    /**
     * الحصول على قيمة إعداد معين
     */
    public function getSetting(string $key): ?string
    {
        try {
            $setting = AccountingSetting::where('key', $key)->first();
            return $setting ? $setting->value : null;
        } catch (Exception $e) {
            Log::error('Error getting accounting setting: ' . $e->getMessage(), [
                'key' => $key
            ]);
            return null;
        }
    }

    /**
     * التحقق من وجود إعداد معين
     */
    public function hasSetting(string $key): bool
    {
        try {
            return AccountingSetting::where('key', $key)->exists();
        } catch (Exception $e) {
            Log::error('Error checking setting existence: ' . $e->getMessage(), [
                'key' => $key
            ]);
            return false;
        }
    }

    /**
     * الحصول على جميع الإعدادات
     */
    public function getAllSettings(): array
    {
        try {
            $settings = AccountingSetting::whereIn('key', array_keys(self::ALLOWED_SETTINGS))
                ->pluck('value', 'key')
                ->toArray();

            return $settings;
        } catch (Exception $e) {
            Log::error('Error getting all accounting settings: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * الحصول على إحصائيات الإعدادات
     */
    public function getSettingsStats(): array
    {
        try {
            $totalSettings = count(self::ALLOWED_SETTINGS);
            $configuredSettings = AccountingSetting::whereIn('key', array_keys(self::ALLOWED_SETTINGS))
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->count();

            return [
                'total' => $totalSettings,
                'configured' => $configuredSettings,
                'missing' => $totalSettings - $configuredSettings,
                'completion_percentage' => $totalSettings > 0 ? round(($configuredSettings / $totalSettings) * 100, 2) : 0
            ];
        } catch (Exception $e) {
            Log::error('Error getting settings stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'configured' => 0,
                'missing' => 0,
                'completion_percentage' => 0
            ];
        }
    }

    /**
     * إعادة تعيين الإعدادات للقيم الافتراضية
     */
    public function reset()
    {
        try {
            DB::beginTransaction();

            // حذف جميع الإعدادات المخزنة
            AccountingSetting::whereIn('key', array_keys(self::ALLOWED_SETTINGS))->delete();

            // حذف أسماء الحسابات أيضاً
            $nameKeys = array_map(fn($key) => $key . '_name', array_keys(self::ALLOWED_SETTINGS));
            AccountingSetting::whereIn('key', $nameKeys)->delete();

            DB::commit();

            Log::info('Accounting settings reset successfully', [
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('success', __('Settings reset successfully'));
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error resetting accounting settings: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', __('Error resetting settings'));
        }
    }

    /**
     * تصدير الإعدادات كـ JSON
     */
    public function export()
    {
        try {
            $settings = $this->getAllSettings();
            $stats = $this->getSettingsStats();

            Log::info('Accounting settings exported', [
                'user_id' => Auth::id(),
                'stats' => $stats
            ]);

            return response()->json([
                'success' => true,
                'settings' => $settings,
                'stats' => $stats,
                'exported_at' => now()->toISOString()
            ]);
        } catch (Exception $e) {
            Log::error('Error exporting accounting settings: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('Error exporting settings')
            ], 500);
        }
    }
}
