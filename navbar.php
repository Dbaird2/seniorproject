<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
<link rel="stylesheet" href="https://dataworks-7b7x.onrender.com/tailwind/output.css">

<style>
text {    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}
.w3-bar,h1,button {    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

</style>
    <style type="text/tailwindcss">
        @layer utilities {
            .text-gradient {
                background: linear-gradient(135deg, #0033A0, #FFD100);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }
            .delay-100 {
                animation-delay: 100ms;
            }
            .delay-200 {
                animation-delay: 200ms;
            }
            .delay-300 {
                animation-delay: 300ms;
            }
        }
        </style>
    <html>
        </head>
        <body class=" aspect-[16/9] sm:aspect-[4/3] md:aspect-[1/1] min-h-screen">
            <!-- Navbar -->

            <nav class="fixed top-0 left-0 w-full py-4 px-6 z-50 bg-gray-700 backdrop-blur-md border-b border-gray-800">

                <div class="max-w-7xl mx-auto flex justify-between items-center">

                    <div class="flex  items-center  space-x-4">
                        <div class="relative">
                            <button id="menu-toggle" class="flex flex-col items-center justify-center w-10 h-10 space-y-1">
                                <span class="block w-8 h-1 bg-white transition-transform duration-300"></span>
                                <span class="block w-8 h-1 bg-white transition-transform duration-300"></span>
                                <span class="block w-8 h-1 bg-white transition-transform duration-300"></span>
                            </button>
                            <div id="menu" class="hidden w-full bg-gray-100 shadow-md">
                                <aside id="menu" class="hidden  absolute w-64  overflow-y-auto bg-gray-800 md:block h-[85vh] left-0">
                                    <div class="py-3 text-2xl uppercase text-center tracking-widest bg-gray-900 border-b-2 border-gray-800 ">
                                        <a href="#" class="text-white"><img src="https://th.bing.com/th/id/OIP.jwU-GwZPqzDTyOxeKaZ2XgHaEz?w=247&h=180&c=7&r=0&o=7&pid=1.7&rm=3" height="80" width="250" />
                                    </a>
                                </div>
                                <nav class="text-sm text-gray-300">
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== '' && $_SESSION['role'] !== NULL) { ?>
                                    <ul class="flex flex-col">
                                        <li class="px-4 cursor-pointer bg-gray-500 text-gray-800 hover:bg-gray-700  hover:text-white">
                                    <a class="py-3 flex items-center" href="https://dataworks-7b7x.onrender.com/index.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="w-4 mr-3">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                    </svg>

                                    Home
                                </a>
                            </li>
                            <li class="px-4 py-2 text-xs uppercase tracking-wider text-gray-500 font-bold">USER MANAGEMENT</li>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>

                                <li class="px-4 cursor-pointer hover:bg-gray-700">
                                    <a class="py-3 flex items-center" href="/">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="w-4 mr-3">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                    </svg>
                                    Admin
                                </a>
                            </li>
                            <?php } ?>
                            <li class="px-4 cursor-pointer hover:bg-gray-700">
                                <a class="py-3 flex items-center" href="/">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-4 mr-3">
            <path stroke-linecap="round" stroke-linejoin="round"
            d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
        </svg>

        Permissions
    </a>
</li>
<li class="px-4  text-xs uppercase tracking-wider text-gray-500 font-bold">PRODUCT MANAGEMENT</li>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>

    <li class="px-4 py-3 cursor-pointer">
        <a id="big-hamb-add" class=" flex items-center" >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-4 mr-3">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122" />
            </svg>

            Add<svg xmlns="http://www.w3.org/2000/svg" id="down-hero-menu" class=" w-[calc(0.4vh+0.7vw)]" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="up-hero-menu" class=" hidden w-[calc(0.4vh+0.7vw)]" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M11.47 7.72a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 1 1-1.06 1.06L12 9.31l-6.97 6.97a.75.75 0 0 1-1.06-1.06l7.5-7.5Z" clip-rule="evenodd" />
            </svg>
            <div id="add-drop-menu" class="hidden text-center relative">
                <div  class="bg-gray-800  dropdown dropdown-start">
                    <ul tabindex="0" id="dropdown-content" class="dropdown-content text-blue-300">
                        <li><a href="https://dataworks-7b7x.onrender.com/add-assets/add-dept.php" class="hover:text-gray-300">Department</a></li>
                        <li><a href="https://dataworks-7b7x.onrender.com/add-assets/add-bldg.php" class="hover:text-gray-300">Building</a></li>
                        <li><a href="https://dataworks-7b7x.onrender.com/add-assets/add-asset.php" class="hover:text-gray-300">Asset</a></li>
                    </ul>
                </div>
            </div>

        </a>
    </li>
    <?php } ?>
    <li class="px-4 cursor-pointer hover:bg-gray-700">
        <a class="py-3 flex items-center" href="https://dataworks-7b7x.onrender.com/search/search.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-4 mr-3">
            <path stroke-linecap="round" stroke-linejoin="round"
            d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
        </svg>

        Search
    </a>
</li>
<li class="px-4 hover:bg-gray-700">
    <a href="https://dataworks-7b7x.onrender.com/audit/upload.php" class="py-3 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
        stroke="currentColor" class="w-4 mr-3">
        <path stroke-linecap="round" stroke-linejoin="round"
        d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
    </svg>
    Audit
</a>
</li>
<li class="px-4 py-2 text-xs uppercase tracking-wider text-gray-500 font-bold">INFORMATION MANAGEMENT</li>
<li class="px-4 hover:bg-gray-700">
    <a href="https://dataworks-7b7x.onrender.com/reports/reports.php" class="py-3 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 mr-3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
        </svg>
        Reports
    </a>
</li>
<li class="px-4 py-2 mt-2 text-xs uppercase tracking-wider text-gray-500 font-bold">OTHER</li>
<li class="px-4 cursor-pointer hover:bg-gray-700">
    <a href="https://dataworks-7b7x.onrender.com/help.php" class="py-2 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 mr-3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
        </svg>

        Help
    </a>
</li>
<li class="px-4 cursor-pointer hover:bg-gray-700">
    <a class="py-3 flex items-center" href="https://dataworks-7b7x.onrender.com/auth/settings.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
        stroke="currentColor" class="w-4 mr-3">
        <path stroke-linecap="round" stroke-linejoin="round"
        d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
    </svg>
    Settings
</a>
</li>
<li class="px-4 cursor-pointer hover:bg-gray-700">
    <a href="https://dataworks-7b7x.onrender.com/auth/logout.php" class="py-2 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 mr-3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
        </svg>

        Logout
    </a>
</li>
<?php } else { ?>
<li class="px-4 cursor-pointer hover:bg-gray-700">
    <a href="https://dataworks-7b7x.onrender.com/auth/logout.php" class="py-2 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 mr-3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
        </svg>
        Login
    </a>
</li>
<?php } ?>
</ul>
</nav>
</aside>
</div>

</div>
</div>

<div class="flex items-center">
    <span class="text-2xl font-bold text-gradient">CSUB.</span>
</div>
<div class="hidden md:flex space-x-8">
    <a href="https://dataworks-7b7x.onrender.com/index.php" class="relative group text-gray-300 hover:text-white transition-colors duration-300">
        <span class="relative">
            Home
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-pink-500 to-purple-600 group-hover:w-full transition-all duration-300"></span>
        </span>
    </a>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] !== '' && $_SESSION['role'] !== NULL) { ?>
    <a href="https://dataworks-7b7x.onrender.com/audit/upload.php" class="relative group text-gray-300 hover:text-white transition-colors duration-300">
        <span class="relative">
            Audit
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-pink-500 to-purple-600 group-hover:w-full transition-all duration-300"></span>
        </span>
    </a>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>

        <a href="#" id="dropdown1" class="relative group text-gray-300 hover:text-white transition-colors duration-300">
            <span class="relative flex"> Add
                <svg xmlns="http://www.w3.org/2000/svg" id="down-hero" class=" w-[calc(0.4vh+0.7vw)]" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                    <path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" />
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="up-hero" class=" hidden w-[calc(0.4vh+0.7vw)]" fill="currentColor" class="size-6">
                    <path fill-rule="evenodd" d="M11.47 7.72a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 1 1-1.06 1.06L12 9.31l-6.97 6.97a.75.75 0 0 1-1.06-1.06l7.5-7.5Z" clip-rule="evenodd" />
                </svg>
            </span>
        </a>
        <?php } ?>
        <a href="https://dataworks-7b7x.onrender.com/search/search.php" class="relative group text-gray-300 hover:text-white transition-colors duration-300">
            <span class="relative">
                Search
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-pink-500 to-purple-600 group-hover:w-full transition-all duration-300"></span>
            </span>
        </a>
        <a href="#" class="relative group text-gray-300 hover:text-white transition-colors duration-300">
            <span class="relative">
                Bob
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-yellow-100 to-yellow-500 group-hover:w-full transition-all duration-300"></span>
            </span>
        </a>
        <?php } ?>
    </div>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] !== '') { 
if ($_SESSION['role'] === 'admin') { ?>
        <div class="flex items-center space-x-4">
            <a href="https://dataworks-7b7x.onrender.com/auth/signup.php" class="relative group text-gray-300 hover:text-white transition-colors duration-300">
                <span class="relative">
                    Signup
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-yellow-100 to-yellow-500 group-hover:w-full transition-all duration-300"></span>
                </span>
            </a>
            <?php } ?>           
            <a href="https://dataworks-7b7x.onrender.com/auth/logout.php" class="relative group text-gray-300 hover:text-white transition-colors duration-300">
                <span class="relative">
                    Logout
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-yellow-100 to-yellow-500 group-hover:w-full transition-all duration-300"></span>
                </span>
            </a>
            <?php } else { ?>
                <a href="https://dataworks-7b7x.onrender.com/auth/login.php" class="relative group text-gray-300 hover:text-white transition-colors duration-300">
                    <span class="relative">
                        Login
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-yellow-100 to-yellow-500 group-hover:w-full transition-all duration-300"></span>
                    </span>
                </a>
                <?php } ?>
            </div>        
        </div>
        <div class="relative">
            <div id="dropdown-menu" class="hidden absolute bg-gray-700 top-0 left-[55vw] w-sm dropdown dropdown-start">
                <ul tabindex="0" id="dropdown-content" class="dropdown-content hover:text-white text-gray-300 z-2 menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm">
                    <li><a href="https://dataworks-7b7x.onrender.com/add-assets/add-dept.php">Department</a></li>
                    <li><a href="https://dataworks-7b7x.onrender.com/add-assets/add-bldg.php">Building</a></li>
                    <li><a href="https://dataworks-7b7x.onrender.com/add-assets/add-asset.php">Asset</a></li>

                </ul>
            </div>
        </div>

    </nav>



    <script>
    // Hero Icons for smaller hamburger menu
    <?php if ($_SESSION['role'] === 'admin') { ?>
    const up_hero = document.getElementById("up-hero");
const down_hero = document.getElementById("down-hero");
const dropdown1 = document.getElementById("dropdown1");
const drop_content = document.getElementById("dropdown-menu");
dropdown1.addEventListener("click", () => {
console.log("clicked dropdown");
drop_content.classList.toggle("hidden");
up_hero.classList.toggle("hidden");
down_hero.classList.toggle("hidden");
});

const up_hero_menu = document.getElementById("up-hero-menu");
const down_hero_menu = document.getElementById("down-hero-menu");
const big_hamb_add = document.getElementById("big-hamb-add");
const add_drop_menu = document.getElementById("add-drop-menu");
big_hamb_add.addEventListener("click", () => {
up_hero_menu.classList.toggle("hidden");
down_hero_menu.classList.toggle("hidden");
add_drop_menu.classList.toggle("hidden");
})
    <?php } ?>

    /*  */
    const menuToggle = document.getElementById("menu-toggle");
const menu = document.getElementById("menu");

menuToggle.addEventListener("click", () => {
menu.classList.toggle("hidden");

// Animate the hamburger icon
const spans = menuToggle.querySelectorAll("span");
spans[0].classList.toggle("opacity-0");
spans[1].classList.toggle("opacity-0");
spans[2].classList.toggle("rotate-45");
});


</script>
</body>
</html>

