<div id="<?php echo $modal_id; ?>" data-ajax-src="<?php echo (isset( $modal_content_ajax ) ) ? $modal_content_ajax : ''; ?>" class="wps-modal-dynamic snapshot-three wps-popup-modal">

	<div class="wps-popup-mask"></div>

	<div class="wps-popup-content">

		<div class="box">

			<div class="box-title can-close">

				<h3><?php echo $modal_title; ?></h3>

				<i class="wps-icon i-close wps-popup-close"></i>

			</div>

			<div class="box-content">

				<div class="row">

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

						<?php echo $modal_content; ?>

							<div class="wps-log-box"></div>

						<?php if ( !empty( $modal_action_title ) || !empty( $modal_cancel_title ) ) { ?>

							<div class="wps-confirmation-buttons">

						<?php if ( !empty( $modal_cancel_title ) ) { ?>

									<a href="<?php echo $modal_cancel_url; ?>" class="wps-popup-close button button-outline button-gray"><?php echo $modal_cancel_title; ?></a>

								<?php } ?>

						<?php if ( !empty( $modal_action_title )) { ?>

									<a href="<?php echo $modal_action_url; ?>" class="button button-blue"><?php echo $modal_action_title; ?></a>

								<?php } ?>

							</div>

						<?php } ?>

					</div>

				</div>

			</div>

		</div>

	</div>

</div>