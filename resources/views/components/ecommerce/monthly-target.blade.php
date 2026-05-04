<div class="rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="shadow-default rounded-2xl bg-white px-5 pb-11 pt-5 dark:bg-gray-900 sm:px-6 sm:pt-6">
        <div class="flex justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Stat par moyens de paiement journalier
                </h3>
            </div>
            <!-- Dropdown Menu -->
            <x-common.dropdown-menu />
            <!-- End Dropdown Menu -->

        </div>
        <div class="relative min-h-[360px]">
            {{-- Chart --}}
            <div id="chartTwo" class="h-full"></div>

        </div>

        <div class="mt-3 text-center">
            <p id="chartTwoTotal" class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl"></p>
        </div>

    </div>
</div>

