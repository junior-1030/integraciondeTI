/**
 * NEXUS TI - Lógica de Interfaz del Sistema
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Selector de Tema (Modo Oscuro / Modo Claro)
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const rootHtml = document.documentElement;
    const currentTheme = localStorage.getItem('nexus_theme') || 'dark';

    rootHtml.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const activeTheme = rootHtml.getAttribute('data-theme');
            const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
            rootHtml.setAttribute('data-theme', newTheme);
            localStorage.setItem('nexus_theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }

    function updateThemeIcon(theme) {
        if (!themeToggleBtn) return;
        const icon = themeToggleBtn.querySelector('i');
        if (icon) {
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    }

    // 2. Control de Sidebar Móvil
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.getElementById('sidebar');

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Cerrar al dar clic fuera en móvil
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992 && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // 3. Modales
    window.openModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            const firstInput = modal.querySelector('input:not([type="hidden"])');
            if (firstInput) setTimeout(() => firstInput.focus(), 150);
        }
    };

    window.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    // Cerrar modal al hacer clic en el backdrop
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // 4. Cargar datos en Modal de Edición
    document.querySelectorAll('.btn-open-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            const email = btn.getAttribute('data-email');
            const telefono = btn.getAttribute('data-telefono') || '';
            const rol = btn.getAttribute('data-rol') || 'Usuario';
            const estado = btn.getAttribute('data-estado') || 'Activo';

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_telefono').value = telefono;
            document.getElementById('edit_rol').value = rol;
            document.getElementById('edit_estado').value = estado;

            openModal('modalEditar');
        });
    });

    // 5. Cargar datos en Modal de Eliminación
    document.querySelectorAll('.btn-open-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');

            document.getElementById('delete_id').value = id;
            document.getElementById('delete_user_name').textContent = nombre;

            openModal('modalEliminar');
        });
    });

    // 6. Búsqueda y Filtros en Tiempo Real
    const searchInput = document.getElementById('searchUsers');
    const filterRole = document.getElementById('filterRole');
    const filterStatus = document.getElementById('filterStatus');
    const tableRows = document.querySelectorAll('#usersTableBody tr:not(.empty-row)');
    const countDisplay = document.getElementById('visibleCount');
    const emptyRow = document.getElementById('noResultsRow');

    function applyFilters() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const roleVal = (filterRole ? filterRole.value : '').toLowerCase();
        const statusVal = (filterStatus ? filterStatus.value : '').toLowerCase();

        let visibleCount = 0;

        tableRows.forEach(row => {
            const name = (row.getAttribute('data-nombre') || '').toLowerCase();
            const email = (row.getAttribute('data-email') || '').toLowerCase();
            const phone = (row.getAttribute('data-telefono') || '').toLowerCase();
            const role = (row.getAttribute('data-rol') || '').toLowerCase();
            const status = (row.getAttribute('data-estado') || '').toLowerCase();

            const matchQuery = !query || name.includes(query) || email.includes(query) || phone.includes(query);
            const matchRole = !roleVal || role === roleVal;
            const matchStatus = !statusVal || status === statusVal;

            if (matchQuery && matchRole && matchStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (countDisplay) {
            countDisplay.textContent = visibleCount;
        }

        if (emptyRow) {
            emptyRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (filterRole) filterRole.addEventListener('change', applyFilters);
    if (filterStatus) filterStatus.addEventListener('change', applyFilters);

    // 7. Exportar a CSV
    const exportBtn = document.getElementById('exportCsvBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "ID,Nombre,Email,Telefono,Rol,Estado,Fecha de Registro\r\n";

            tableRows.forEach(row => {
                if (row.style.display !== 'none') {
                    const id = row.getAttribute('data-id') || '';
                    const nombre = `"${(row.getAttribute('data-nombre') || '').replace(/"/g, '""')}"`;
                    const email = `"${(row.getAttribute('data-email') || '').replace(/"/g, '""')}"`;
                    const telefono = `"${(row.getAttribute('data-telefono') || '').replace(/"/g, '""')}"`;
                    const rol = `"${(row.getAttribute('data-rol') || '').replace(/"/g, '""')}"`;
                    const estado = `"${(row.getAttribute('data-estado') || '').replace(/"/g, '""')}"`;
                    const fecha = `"${(row.getAttribute('data-fecha') || '').replace(/"/g, '""')}"`;

                    csvContent += [id, nombre, email, telefono, rol, estado, fecha].join(",") + "\r\n";
                }
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `usuarios_nexus_${new Date().toISOString().slice(0, 10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }

    // 8. Auto-cerrar Notificaciones Toast después de 4.5s
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        setTimeout(() => {
            toast.style.transition = 'all 0.5s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 500);
        }, 4500);
    });
});
