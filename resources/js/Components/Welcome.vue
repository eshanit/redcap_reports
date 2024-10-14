<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div>
        <div class="p-6 bg-white border-b border-gray-200 lg:p-8">
            <ApplicationLogo class="block w-auto h-12" />
             

            <h1 class="mt-8 text-2xl font-medium text-gray-900">
                Welcome to your RedCap Reports App! <span class="text-orange-500">Stream</span>
            </h1>

            <p class="mt-6 leading-relaxed text-gray-500">
                Stream provides a beautiful, robust starting point for your RedCap Data. It is designed
                to help you access your data in a simple, powerful, and enjoyable way. We believe you should love getting insights from your surveys,
                so we have spent time carefully crafting the Stream
                ecosystem to be a breath of fresh air. We hope you love it.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 bg-gray-200 bg-opacity-25 md:grid-cols-2 lg:gap-8 lg:p-8">
            <div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900 ms-3">
                        <a href="https://laravel.com/docs">Insights</a>
                    </h2>
                </div>

                <p class="mt-4 text-sm leading-relaxed text-gray-500">
                    Stream has wonderful data Insights covering every aspect of your project. In a snapshot it shows you how much data you have collected and breaks this down by instruments, events even down to each field/question.
                </p>

                <p class="mt-4 text-sm">
                    <a href="#" class="inline-flex items-center font-semibold text-indigo-700">
                        Explore the Insights

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="w-5 h-5 ms-1 fill-indigo-500">
                            <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </p>
            </div>

            <div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                        <path stroke-linecap="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900 ms-3">
                        <a href="https://laracasts.com">Statistics</a>
                    </h2>
                </div>

                <p class="mt-4 text-sm leading-relaxed text-gray-500">
                    Stream offers importsn statistics and analysis on your survey items. Check them out, see for yourself, and massively level up your development skills in the process.
                </p>

                <p class="mt-4 text-sm">
                    <a href="3" class="inline-flex items-center font-semibold text-indigo-700">
                        View your statistics

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="w-5 h-5 ms-1 fill-indigo-500">
                            <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </p>
            </div>

            <div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900 ms-3">
                        <a href="https://tailwindcss.com/">Modules</a>
                    </h2>
                </div>

                <p class="mt-4 text-sm leading-relaxed text-gray-500">
                   Stream offers customised modules for indepth analysis. All focus will be project based and as per your needs. There are available ready made modules and also others by request.
                </p>
            </div>

            <div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900 ms-3">
                        Data Querying
                    </h2>
                </div>

                <p class="mt-4 text-sm leading-relaxed text-gray-500">
                    A very powerful tool which gives you access to any data you need. Its an easy to use too which lets you have your data blazingly fast. Your daa is handed to you in grid format allowing you to filter and sort it accordingly.
                </p>
            </div>
        </div>
    </div>
</template>
