
		<!--begin::Javascript-->
		<script>var hostUrl = "assets/";</script>
		<!--begin::Global Javascript Bundle(mandatory for all pages)-->
		<script src="<?= base_url('assets/themes/metronic38/assets/plugins/global/plugins.bundle.js'); ?>"></script>
		<script src="<?= base_url('assets/themes/metronic38/assets/js/scripts.bundle.js'); ?>"></script>
		<!--end::Global Javascript Bundle-->

		<script>
			var BASE_URL = "<?php echo base_url(); ?>";
		</script>

		<script>
			const env = '<?php echo ENVIRONMENT; ?>';
			if(env === 'production'){
				console.log = function() {};
			}
		</script>

		<?php if(isset($jsSrc)){ ?>
            <?php foreach($jsSrc as $js): ?>
                <script src="<?= base_url($js) ?>"></script>
            <?php endforeach; ?>
        <?php } ?>