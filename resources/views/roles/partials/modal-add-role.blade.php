<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-simple modal-dialog-centered modal-add-new-role">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-6">
                    <h4 class="role-title" id="roleModalTitle">{{ __('Add New Role') }}</h4>
                    <p class="text-body-secondary">{{ __('Set role permissions') }}</p>
                </div>
                <!-- Add/Edit role form -->
                <form id="roleModalForm" class="row g-3">
                    @csrf
                    <input type="hidden" name="role_id" id="roleIdInput" value="">
                    <div class="col-12 mb-3">
                        <label class="form-label" for="modalRoleName">{{ __('Role Name') }}</label>
                        <input type="text" id="modalRoleName" name="name" class="form-control" required />
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Permissions') }}</label>
                        <div id="permissionsTableContainer"></div>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary"
                            id="roleModalSubmitBtn">{{ __('Save') }}</button>
                        <button type="button" class="btn btn-label-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    </div>
                </form>
                <!--/ Add/Edit role form -->
            </div>
        </div>
    </div>
</div>
