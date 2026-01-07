<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartBin Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .sidebar-admin-label { color: #fff !important; }
        body.dark, .dark body {
            background-color: #18181b !important;
        }
        .dark .bg-[#fafbfc] { background-color: #18181b !important; }
        .dark .bg-white { background-color: #23272f !important; }
        .dark .text-gray-800, .dark .text-gray-700 { color: #e5e7eb !important; }
        .dark .text-[#f6c90e] { color: #f6c90e !important; }
        .dark .border-gray-200 { border-color: #333 !important; }
        .dark .shadow, .dark .shadow-lg, .dark .shadow-sm { box-shadow: none !important; }
        .dark .bg-[#fff7e6] { background-color: #23272f !important; }
        .dark .bg-gradient-to-r { background-image: linear-gradient(to right, #23272f, #23272f) !important; }
        .dark .text-white { color: #f6c90e !important; }
        .dark .rounded-xl, .dark .rounded-2xl { border: 1px solid #333 !important; }
        .dark input, .dark select, .dark textarea { background-color: #23272f !important; color: #e5e7eb !important; }
        .dark .hover\:bg-[#fff7e6]:hover { background-color: #23272f !important; }
    </style>
    @vite('resources/css/app.css')
</head>
<body class="bg-[#fafbfc] dark:bg-[#18181b] min-h-screen">
    @yield('content')
    <script>
        // Dark mode toggle logic
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');
        function setDarkMode(on) {
            if (on) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('darkMode', '1');
                darkModeIcon.textContent = '☀️';
                darkModeToggle.textContent = 'Light Mode';
                darkModeToggle.prepend(darkModeIcon);
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('darkMode', '0');
                darkModeIcon.textContent = '🌙';
                darkModeToggle.textContent = 'Dark Mode';
                darkModeToggle.prepend(darkModeIcon);
            }
        }
        darkModeToggle.addEventListener('click', function() {
            setDarkMode(!document.documentElement.classList.contains('dark'));
        });
        // On load, set mode from localStorage
        if (localStorage.getItem('darkMode') === '1') {
            setDarkMode(true);
        } else {
            setDarkMode(false);
        }
    </script>
    @yield('scripts')
</body>
</html>
