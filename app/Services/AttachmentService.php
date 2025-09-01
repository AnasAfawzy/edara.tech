<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\AttachmentFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    protected $attachmentModel;
    protected $attachmentFileModel;

    public function __construct(Attachment $attachmentModel, AttachmentFile $attachmentFileModel)
    {
        $this->attachmentModel = $attachmentModel;
        $this->attachmentFileModel = $attachmentFileModel;
    }

    /**
     * Uploads and associates attachments with a given model.
     *
     * @param Model $attachable The model to attach files to (e.g., JournalEntry).
     * @param array $files An array of UploadedFile instances.
     * @param string|null $description An optional description for the attachment group.
     * @return Attachment|null The created Attachment model, or null if no files were uploaded.
     * @throws \Exception
     */
    public function uploadAttachments(Model $attachable, array $files, ?string $description = null): ?Attachment
    {
        if (empty($files)) {
            return null;
        }

        return DB::transaction(function () use ($attachable, $files, $description) {
            $attachment = $this->attachmentModel->create([
                'attachable_id' => $attachable->id,
                'attachable_type' => $attachable::class,
                'description' => $description,
            ]);

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $filePath = $file->store('attachments/' . $attachable->getTable(), 'public');

                    $this->attachmentFileModel->create([
                        'attachment_id' => $attachment->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            return $attachment->fresh('files');
        });
    }

    /**
     * Deletes an attachment group and its associated files.
     *
     * @param int $attachmentId The ID of the attachment group to delete.
     * @return bool True if deletion was successful, false otherwise.
     * @throws \Exception
     */
    public function deleteAttachment(int $attachmentId): bool
    {
        $attachment = $this->attachmentModel->with('files')->find($attachmentId);

        if (!$attachment) {
            return false;
        }

        return DB::transaction(function () use ($attachment) {
            foreach ($attachment->files as $file) {
                $this->deleteSingleAttachmentFile($file);
            }
            return $attachment->delete();
        });
    }

    /**
     * Deletes a single attachment file.
     *
     * @param int $fileId The ID of the attachment file to delete.
     * @return bool True if deletion was successful, false otherwise.
     */
    public function deleteAttachmentFile(int $fileId): bool
    {
        $file = $this->attachmentFileModel->find($fileId);

        if (!$file) {
            return false;
        }

        return DB::transaction(function () use ($file) {
            $this->deleteSingleAttachmentFile($file);

            // If the parent attachment group has no more files, delete it too
            if ($file->attachment && $file->attachment->files->count() === 0) {
                $file->attachment->delete();
            }
            return true;
        });
    }

    /**
     * Deletes a single attachment file and its physical file from storage.
     *
     * @param AttachmentFile $file The attachment file instance to delete.
     * @return void
     */
    private function deleteSingleAttachmentFile(AttachmentFile $file): void
    {
        Storage::disk('public')->delete($file->file_path);
        $file->delete();
    }

    /**
     * Retrieves all attachments for a given model.
     *
     * @param Model $attachable The model to retrieve attachments for.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAttachments(Model $attachable): \Illuminate\Database\Eloquent\Collection
    {
        return $attachable->attachments()->with('files')->get();
    }

    /**
     * Retrieves a single attachment file.
     *
     * @param int $fileId The ID of the attachment file to retrieve.
     * @return AttachmentFile|null
     */
    public function getAttachmentFile(int $fileId): ?AttachmentFile
    {
        return $this->attachmentFileModel->find($fileId);
    }
}
