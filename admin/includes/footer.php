<?php
// La variable $baseURL fue definida en header.php, así que podemos reusarla.
?>
</div>
<?php if ($current_page === 'configuracion'): ?>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<?php endif; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="<?php echo $baseURL; ?>admin/assets/js/admin-script.js?v=<?php echo time(); ?>"></script>
</body>
</html>