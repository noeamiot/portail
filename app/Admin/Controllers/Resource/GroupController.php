<?php
/**
 * Manage Groups as admin.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2018, SiMDE-UTC
 * @license GNU GPL-3.0
 */

namespace App\Admin\Controllers\Resource;

use App\Models\{
    Group, User, Visibility
};

class GroupController extends ResourceController
{
    protected $model = Group::class;

    /**
     * Fields to display definition.
     *
     * @return array
     */
    protected function getFields(): array
    {
        return [
            'id' => 'display',
            'user' => User::get(['id', 'lastname', 'firstname']),
            'icon' => 'text',
            'visibility' => Visibility::get(['id', 'name']),
            'created_at' => 'display',
            'updated_at' => 'display',
        ];
    }

    /**
     * Default values definition of the fields to display.
     *
     * @return array
     */
    protected function getDefaults(): array
    {
        return [
            'user_id' => \Auth::guard('admin')->user()->id,
            'visibility_id' => Visibility::first()->id,
        ];
    }

    /**
     * Return dependencies.
     *
     * @return array
     */
    protected function getWith(): array
    {
        return [
            'user', 'visibility'
        ];
    }
}
