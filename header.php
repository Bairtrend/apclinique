<header class="bg-green-600 text-white py-4 shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <!-- Logo et Nom de la clinique -->
        <div class="flex items-center space-x-3">
            <img src="images/logo.png" alt="Logo Clinique" class="w-12 h-12 rounded-full">
            <div>
                <h1 class="text-3xl font-bold">Clinique Santé</h1>
                <p class="text-sm">Votre bien-être, notre priorité</p>
            </div>
        </div>

        <!-- Connexion et Inscription -->
        <div class="space-x-4">
            <a href="index.php" class="bg-white text-green-600 font-bold py-2 px-4 rounded-lg hover:bg-green-100 transition">Connexion</a>
        </div>

        <!-- Menu Mobile -->
        <div class="md:hidden">
            <button id="mobile-menu-button" class="text-white focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                </svg>
            </button>
        </div>
    </div>

    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
</header>
