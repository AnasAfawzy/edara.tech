<?php


namespace App\Http\Controllers;

use App\Models\CostCenter;
use Illuminate\Http\Request;
use App\Services\CostCenterService;
use App\Repositories\CostCenterRepository;
use App\Services\TreeCodeGeneratorService;

class CostCenterController extends Controller
{
    protected $costCenterService;
    protected $repository;
    protected $treeCodeGeneratorService;

    public function __construct(
        CostCenterService $costCenterService,
        TreeCodeGeneratorService $treeCodeGeneratorService,
        CostCenterRepository $repository
    ) {
        $this->costCenterService = $costCenterService;
        $this->repository = $repository;
        $this->treeCodeGeneratorService = $treeCodeGeneratorService;
    }

    public function index()
    {
        $costCenters = $this->costCenterService->getCostCenterTree();
        $jsTreeData = $this->buildJsTreeFlat($costCenters);

        $allCostCenters = $this->repository->getModel()
            ->where('slave', 0)
            ->orWhere('has_sub', 1)
            ->get();

        return view('CostCenters.index', [
            'costCenters'    => json_encode($jsTreeData, JSON_UNESCAPED_UNICODE),
            'allCostCenters' => $allCostCenters
        ]);
    }

    public function treeData()
    {
        $costCenters = $this->costCenterService->getCostCenterTree();
        return response()->json($this->buildJsTreeFlat($costCenters));
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $data = $this->mapTypeData($data);

        try {
            $costCenter = $this->costCenterService->createCostCenter($data);

            $node = $this->buildJsTreeNode($costCenter);

            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => true, 'message' => __('Cost Center creation successful'), 'node' => $node, 'data' => $costCenter])
                : redirect()->route('cost_centers.index')->with('success', __('Cost Center created successfully'));
        } catch (\Exception $e) {
            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 400)
                : redirect()->route('cost_centers.index')->with('error', $e->getMessage());
        }
    }

    public function edit(CostCenter $costCenter)
    {
        $parent_details_html = '';
        if ($costCenter->ownerEl) {
            $parent = $this->repository->find($costCenter->ownerEl);
            if ($parent) {
                $parentType = $parent->has_sub ? __('title') : ($parent->slave ? __('sub Account') : __('Account'));

                $parent_details_html = sprintf(
                    '<b>%s:</b> %s<br><b>%s:</b> %s<br><b>%s:</b> %s',
                    __('Cost Center Name'),
                    e($parent->name),
                    __('Cost Center Code'),
                    e($parent->code),
                    __('Cost Center Type'),
                    $parentType
                );
            }
        }

        $type = $this->resolveCostCenterType($costCenter);

        return response()->json([
            'success'            => true,
            'cost_center'        => $costCenter,
            'has_children'       => $costCenter->children()->exists(),
            'is_sub_account'      => $type === 'account' || $type === 'sub_account',
            'is_title'           => $type === 'title',
            'type'               => $type,
            'parent_details_html' => $parent_details_html,
        ]);
    }

    public function update(Request $request, CostCenter $costCenter)
    {
        $data = $this->validateRequest($request);
        $data = $this->mapTypeData($data);

        try {
            $this->costCenterService->update($costCenter->id, $data);

            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => true, 'message' => __('Cost Center updated successfully')])
                : redirect()->route('cost_centers.index')->with('success', __('Cost Center updated successfully'));
        } catch (\Exception $e) {
            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 400)
                : redirect()->route('cost_centers.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(CostCenter $costCenter)
    {
        try {
            $this->costCenterService->delete($costCenter->id);

            return response()->json([
                'success' => true,
                'message' => __('Cost Center deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getCostCenterDeleteInfo(CostCenter $costCenter)
    {
        return response()->json([
            'success'      => true,
            'has_children' => $costCenter->children()->exists(),
            'cost_center'  => $costCenter,
        ]);
    }

    // ==============================
    // 🔹 Helpers
    // ==============================

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'name'      => 'required|string|max:100',
            'parent_id' => 'nullable|exists:cost_centers,id',
            'type'      => 'required|in:account,sub_account,title',
        ]);
    }

    private function mapTypeData(array $data): array
    {
        if (isset($data['parent_id'])) {
            $data['ownerEl'] = $data['parent_id'];
        }

        switch ($data['type']) {
            case 'account':
                $data['slave'] = 1;
                $data['has_sub'] = 0;
                break;
            case 'sub_account':
                $data['slave'] = 0;
                $data['has_sub'] = 1;
                break;
            default:
                $data['slave'] = 0;
                $data['has_sub'] = 0;
        }

        return $data;
    }

    private function resolveCostCenterType(CostCenter $costCenter): string
    {
        if ($costCenter->slave && !$costCenter->has_sub) {
            return 'account';
        } elseif (!$costCenter->slave && $costCenter->has_sub) {
            return 'sub_account';
        }
        return 'title';
    }

    private function buildJsTreeFlat($costCenters)
    {
        return $costCenters->map(fn($center) => $this->buildJsTreeNode($center));
    }

    private function buildJsTreeNode(CostCenter $center)
    {
        if ($center->has_sub == 1) {
            $icon = 'icon-base ti tabler-file-spark';
            $class = 'jstree-red';
            $type  = 'sub_account';
        } elseif ($center->slave == 1) {
            $icon = 'icon-base ti tabler-file';
            $class = 'jstree-blue';
            $type  = 'account';
        } else {
            $icon = 'icon-base ti tabler-folder';
            $class = '';
            $type  = 'title';
        }

        return [
            'id'     => $center->id,
            'parent' => $center->ownerEl ?: '#',
            'text'   => $center->name,
            'icon'   => $icon,
            'type'   => $type,
            'code'   => $center->code,
            'li_attr' => ['class' => $class]
        ];
    }
}
