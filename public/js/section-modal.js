function showEditModal(section) {
    document.getElementById('edit_name').value = section.name;
    document.getElementById('edit_description').value = section.description;

    const form = document.getElementById('editForm');
    form.action = `/sections/${section.id}`;

    const editModal = new bootstrap.Modal(document.getElementById('editSectionModal'));
    editModal.show();
}

function showDeleteModal(sectionId) {
    const form = document.getElementById('deleteForm');
    form.action = `/sections/${sectionId}`;

    const deleteModal = new bootstrap.Modal(document.getElementById('deleteSectionModal'));
    deleteModal.show();
}
