document.addEventListener("DOMContentLoaded", function() {
    const preloader = document.querySelector('.preloader');
    const navLinks = document.querySelectorAll('.nav-item');
    
    // Logika untuk menandai item navigasi yang aktif
    const currentPage = window.location.pathname.split("/").pop(); // Mendapatkan nama file, cth: "home.php"
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage) {
            link.classList.add('active');
        }
    });

    // Tambahkan event listener ke semua link navigasi untuk efek preloader
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah perpindahan halaman instan
            const destination = this.href;

            // Jangan jalankan preloader jika link yang diklik adalah halaman saat ini
            if (destination === window.location.href) {
                return;
            }

            preloader.classList.add('show');

            // Tunggu sebentar lalu pindah halaman
            setTimeout(() => {
                window.location.href = destination;
            }, 500); // 0.5 detik untuk efek transisi
        });
    });

    // Handle navigasi back/forward dari browser
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            preloader.classList.remove('show');
        }
    });
});

