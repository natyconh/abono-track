<?php
// _legacy/abono-track/app/views/layout/footer.php
?>
</main> <!-- Fin del contenedor principal -->
    </div> <!-- Fin del d-flex -->

    <!-- Footer -->
    <footer class="py-4 mt-auto bg-white border-top">
        <div class="container-fluid text-center">
            <p class="mb-0 small text-muted font-monospace" style="font-size: 0.75rem;">
                <span class="fw-bold text-primary-dark-green">abono·track</span>
                <span class="mx-2">|</span>
                &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>
                <span class="mx-2">·</span>
                 Desarrollado por Cristian Manzano y Nathalia Castro
                <span class="mx-2">·</span>
                v0.2.0
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <?php if (isset($use_datatables) && $use_datatables): ?>
        <script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
        <script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.js"></script>
        <script>
            $(document).ready(function() {
                $('#tablaUsuarios, .datatable').DataTable({
                    language: { url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json' },
                    responsive: true
                });
            });
        </script>
    <?php endif; ?>

    <?php if (isset($use_charts) && $use_charts): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>

    <script src="<?php echo URL_ROOT; ?>/js/main.js"></script>
</body>
</html>
