<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TreeCodeGeneratorService
{
    public function generate(array $data, Model $repositoryModel): array
    {
        $row_tree = [
            'ownerEl'  => $data['ownerEl'] ?? null,
            'position' => isset($data['position']) ? (int)$data['position'] : 0,
        ];

        $row_parent = null;
        $parent_code = '';

        if (!empty($row_tree['ownerEl'])) {
            $row_parent = $repositoryModel->find($row_tree['ownerEl']);
            if ($row_parent) {
                $parent_code = $row_parent->code;
            }
            $maxPosition = $repositoryModel->newQuery()
                ->where('ownerEl', $row_tree['ownerEl'])
                ->max('position');
            $position = is_null($maxPosition) ? 1 : $maxPosition + 1;
        } else {
            $maxPosition = $repositoryModel->newQuery()
                ->whereNull('ownerEl')
                ->orWhere('ownerEl', 0)
                ->max('position');
            $position = is_null($maxPosition) ? 1 : $maxPosition + 1;
        }

        $code = str_pad($position, 2, '0', STR_PAD_LEFT);
        $code = $parent_code . $code;

        $level = strlen($code) / 2;

        $data['position'] = $position;
        $data['code']     = $code;
        $data['level']    = $level;

        return $data;
    }
}
