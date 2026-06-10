@push('scripts')
<script>
const roleSelect = document.querySelector('select[name="role"]');
const sections = document.querySelectorAll('[data-role-section]');
function syncRoleSections() {
    const role = roleSelect.value;
    sections.forEach((section) => {
        const allowed = section.dataset.roleSection.split(' ').includes(role);
        section.classList.toggle('d-none', !allowed);
        section.querySelectorAll('input, select, textarea').forEach((field) => field.disabled = !allowed);
    });
}
roleSelect?.addEventListener('change', syncRoleSections);
syncRoleSections();
</script>
@endpush