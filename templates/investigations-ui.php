<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap" id="cp-investigations-app">
	<h1><?php _e( 'Investigations', 'changeproof' ); ?></h1>
	<p class="description"><?php _e( 'Investigation mode groups your changes under a specific audit session.', 'changeproof' ); ?></p>

	<?php if ( $active_id ) : ?>
		<!-- End Investigation UI -->
		<div class="notice notice-info inline" style="border-left-color: #2271b1; padding: 20px; margin-top: 20px;">
			<h2><?php _e( 'Investigation Active', 'changeproof' ); ?> (ID: #<?php echo esc_html( $active_id ); ?>)</h2>
			<p><?php _e( 'All changes you make will be tagged until you end this session.', 'changeproof' ); ?></p>
			
			<textarea id="cp-final-note" class="large-text" placeholder="<?php _e( 'Final summary or conclusion...', 'changeproof' ); ?>"></textarea>
			<p>
				<button id="cp-btn-end-investigation" class="button button-primary" data-nonce="<?php echo wp_create_nonce('cp_investigation_nonce'); ?>">
					<span class="dashicons dashicons-yes" style="margin-right:6px;"></span>
					<?php _e( 'End Investigation', 'changeproof' ); ?>
				</button>
			</p>
		</div>
	<?php else : ?>
		<!-- Start Investigation UI -->
		<div class="card" style="max-width: 600px; margin-top: 20px;">
			<h2><?php _e( 'Start New Investigation', 'changeproof' ); ?></h2>
			<p><?php _e( 'Provide an initial note to describe the purpose of this audit.', 'changeproof' ); ?></p>
			
			<textarea id="cp-start-note" class="large-text" placeholder="<?php _e( 'e.g., Auditing security settings on landing pages.', 'changeproof' ); ?>"></textarea>
			<p>
				<button id="cp-btn-start-investigation" class="button button-primary" data-nonce="<?php echo wp_create_nonce('cp_investigation_nonce'); ?>">
					<span class="dashicons dashicons-flag" style="margin-right:6px;"></span>
					<?php _e( 'Start Recording', 'changeproof' ); ?>
				</button>
			</p>
		</div>
	<?php endif; ?>

	<h2 style="margin-top: 40px;"><?php _e( 'Your History', 'changeproof' ); ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th width="80"><?php _e( 'ID', 'changeproof' ); ?></th>
				<th><?php _e( 'Status', 'changeproof' ); ?></th>
				<th><?php _e( 'Start Date', 'changeproof' ); ?></th>
				<th><?php _e( 'End Date', 'changeproof' ); ?></th>
				<th><?php _e( 'Notes', 'changeproof' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $history ) ) : ?>
				<tr><td colspan="5"><?php _e( 'No investigations found.', 'changeproof' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $history as $inv ) : ?>
					<tr>
						<td>#<?php echo esc_html( $inv->id ); ?></td>
						<td><span class="status-badge <?php echo esc_attr($inv->status); ?>"><?php echo esc_html( strtoupper($inv->status) ); ?></span></td>
						<td><?php echo esc_html( $inv->start_time ); ?></td>
						<td><?php echo esc_html( $inv->end_time ? $inv->end_time : '-' ); ?></td>
						<td>
							<strong><?php _e( 'Initial:', 'changeproof' ); ?></strong> <?php echo esc_html( $inv->initial_note ); ?><br>
							<?php if ( $inv->final_note ) : ?>
								<strong><?php _e( 'Final:', 'changeproof' ); ?></strong> <?php echo esc_html( $inv->final_note ); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
