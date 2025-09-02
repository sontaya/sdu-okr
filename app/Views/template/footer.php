
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



		<!--begin::Extra JavaScript (from controller)-->
		<?php if(isset($extra_js) && !empty($extra_js)): ?>
			<script>
				$(document).ready(function() {
					<?php if(is_array($extra_js)): ?>
						<?php foreach($extra_js as $js): ?>
					<?= $js ?>
						<?php endforeach; ?>
					<?php else: ?>
					<?= $extra_js ?>
					<?php endif; ?>
				});
			</script>
		<?php endif; ?>
		<!--end::Extra JavaScript-->

		<!--begin::Raw JavaScript (executed without document ready wrapper)-->
		<?php if(isset($raw_js) && !empty($raw_js)): ?>
		<script>
			<?php if(is_array($raw_js)): ?>
				<?php foreach($raw_js as $js): ?>
			<?= $js ?>
				<?php endforeach; ?>
			<?php else: ?>
			<?= $raw_js ?>
			<?php endif; ?>
			</script>
		<?php endif; ?>
		<!--end::Raw JavaScript-->