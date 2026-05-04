<div x-data="{
            period: 'Personnalisé',
            customStart: '',
            customEnd: ''
        }" x-init="$nextTick(() => {
            const today = new Date();
            const startDate = new Date(today);
            startDate.setDate(today.getDate() - 29);
            customStart = startDate.toLocaleDateString('fr-FR');
            customEnd = today.toLocaleDateString('fr-FR');

            if (window.flatpickr) {
                flatpickr($refs.customStart, { dateFormat: 'd/m/Y' });
                flatpickr($refs.customEnd, { dateFormat: 'd/m/Y' });
            }
        })" x-cloak class="rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="shadow-default rounded-2xl bg-white px-5 pb-8 pt-4 dark:bg-gray-900 sm:px-6 sm:pt-5">
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
        <div class="relative min-h-[220px] flex justify-center">
            {{-- Chart --}}
            <div id="chartTwo" class="h-full w-full max-w-[420px] mx-auto"></div>

        </div>

        <div class="mt-2 text-center">
            <p id="chartTwoTotal" class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl"></p>
        </div>
    </div>
</div>

