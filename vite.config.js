import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    base: '/build/',
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/scss/app.scss',
                'resources/scss/icons.scss',

                'node_modules/daterangepicker/daterangepicker.css',
                'node_modules/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css',
                'node_modules/quill/dist/quill.core.css',
                'node_modules/quill/dist/quill.snow.css',
                'node_modules/quill/dist/quill.bubble.css',
                'node_modules/jquery-toast-plugin/dist/jquery.toast.min.css',
                'node_modules/select2/dist/css/select2.min.css',
                'node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.css',
                'node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css',
                'node_modules/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
                'node_modules/flatpickr/dist/flatpickr.min.css',
                'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css',
                'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css',
                'node_modules/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css',
                'node_modules/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css',
                'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css',
                'node_modules/datatables.net-select-bs5/css/select.bootstrap5.min.css',

                'resources/js/app.js',
                'resources/js/head.js',
                'resources/js/layout.js',

                'node_modules/jquery/dist/jquery.js',
                'node_modules/daterangepicker/moment.min.js',
                'node_modules/dragula/dist/dragula.min.js',
                'node_modules/jquery-toast-plugin/dist/jquery.toast.min.js',
                'node_modules/jquery.rateit/scripts/jquery.rateit.min.js',

                'node_modules/datatables.net/js/jquery.dataTables.min.js',
                'node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js',
                'node_modules/datatables.net-responsive/js/dataTables.responsive.min.js',
                'node_modules/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js',
                'node_modules/jquery-datatables-checkboxes/js/dataTables.checkboxes.min.js',
                'node_modules/datatables.net-buttons/js/dataTables.buttons.min.js',
                'node_modules/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js',
                'node_modules/datatables.net-buttons/js/buttons.html5.min.js',
                'node_modules/datatables.net-buttons/js/buttons.flash.min.js',
                'node_modules/datatables.net-buttons/js/buttons.print.min.js',
                'node_modules/datatables.net-keytable/js/dataTables.keyTable.min.js',
                'node_modules/datatables.net-select/js/dataTables.select.min.js',
                'node_modules/select2/dist/js/select2.min.js',
                'node_modules/jquery-toast-plugin/src/jquery.toast.js',
                'node_modules/cleave.js/dist/cleave.js',

                'node_modules/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js',
                'node_modules/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js',
                'bootstrap-timepicker/js/bootstrap-timepicker.min.js',
                'node_modules/jquery-mask-plugin/dist/jquery.mask.min.js',

                'typeahead.js/dist/typeahead.bundle.js',
                'typeahead.js/dist/typeahead.bundle.min.js',
                'jquery.rateit/scripts/jquery.rateit.js',

                'resources/js/customs/patient-check-form.js',
                'resources/js/customs/datatable.js',
                'resources/js/customs/dashboard.js',
                'resources/js/customs/appointments.js',
                'resources/js/customs/clinics.js',
                'resources/js/customs/schedule-dates.js',
                'resources/js/customs/schedules.js',
                'resources/js/customs/users.js',
                'resources/js/customs/business-partners.js',
                'resources/js/customs/qrcarolus.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        sourcemap: false,
    },
    resolve: {
        alias: {
            $: "jQuery",
        },
    },
});
