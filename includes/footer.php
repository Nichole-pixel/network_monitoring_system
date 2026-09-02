<!-- SCRIPTS -->
<script>
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle("open");
        sidebar.classList.remove("collapsed");
    } else {
        sidebar.classList.toggle("collapsed");
        sidebar.classList.remove("open");
    }
}

// Logout modal
const logoutLink = document.querySelector('.logout');
const logoutModal = document.getElementById('logoutModal');
const confirmBtn = document.getElementById('confirmLogout');
const cancelBtn = document.getElementById('cancelLogout');

if (logoutLink && logoutModal) {
    logoutLink.addEventListener('click', function(e) {
        e.preventDefault();
        logoutModal.style.display = 'flex';
    });

    confirmBtn.addEventListener('click', function() {
        window.location.href = logoutLink.href;
    });

    cancelBtn.addEventListener('click', function() {
        logoutModal.style.display = 'none';
    });

    window.addEventListener('click', function(e) {
        if (e.target == logoutModal) {
            logoutModal.style.display = 'none';
        }
    });

    // Close modal on Esc key
    window.addEventListener('keydown', function(e) {
        if (e.key === "Escape") {
            logoutModal.style.display = 'none';
        }
    });
}
</script>
</body>
</html>
