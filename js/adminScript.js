document.addEventListener('DOMContentLoaded', function() {
    const toggleSidebar = document.getElementById('toggleSidebar');
    const adminSidebar = document.getElementById('adminSidebar');

    if (toggleSidebar && adminSidebar) {
        toggleSidebar.addEventListener('click', function() {
            adminSidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        // document.addEventListener('click', function(event) {
        //     if (window.innerWidth < 992 &&
        //         !adminSidebar.contains(event.target) &&
        //         !toggleSidebar.contains(event.target) &&
        //         adminSidebar.classList.contains('show')) {
        //         adminSidebar.classList.remove('show');
        //     }
        // });
    }
});
document.querySelectorAll('.btn-delete-confirm').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm("Are you sure you want to delete this post?")) {
            e.preventDefault();
        }
    });
});