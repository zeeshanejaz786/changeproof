<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Changeproof Timeline', 'changeproof' ); ?></h1>
	<hr class="wp-header-end">

	<table class="wp-list-table widefat fixed striped table-view-list cp-timeline-table">
		<thead>
			<tr>
				<th style="width:14%"><?php _e( 'Date / Context', 'changeproof' ); ?></th>
				<th style="width:14%"><?php _e( 'User', 'changeproof' ); ?></th>
				<th style="width:18%"><?php _e( 'Action', 'changeproof' ); ?></th>
				<th style="width:22%"><?php _e( 'Reason / Intent', 'changeproof' ); ?></th>
				<th><?php _e( 'Change Snapshot', 'changeproof' ); ?></th>
			</tr>
		</thead>

		<tbody>
		<?php if ( empty( $changes ) ) : ?>
			<tr>
				<td colspan="5" style="text-align:center;">
					<?php _e( 'No changes recorded yet.', 'changeproof' ); ?>
				</td>
			</tr>
		<?php else : ?>
			<?php foreach ( $changes as $change ) : ?>
				<tr class="<?php echo $change->investigation_id ? 'cp-has-investigation' : ''; ?>">

					<!-- Date / Investigation -->
					<td>
						<strong><?php echo esc_html( $change->created_at ); ?></strong>

						<?php if ( $change->investigation_id ) : ?>
							<div class="cp-investigation-badge">
								<?php _e( 'Investigation', 'changeproof' ); ?> #<?php echo esc_html( $change->investigation_id ); ?>
								<?php if ( ! empty( $change->investigation_status ) ) : ?>
									<span class="cp-inv-status">
										(<?php echo esc_html( ucfirst( $change->investigation_status ) ); ?>)
									</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</td>

					<!-- User -->
					<td>
						<?php echo esc_html( $change->display_name ?: __( 'System', 'changeproof' ) ); ?>
					</td>

					<!-- Action -->
					<td>
						<span class="dashicons <?php echo esc_attr( cp_get_change_icon( $change->change_type ) ); ?>"></span>
						<strong><?php echo esc_html( cp_get_change_label( $change->change_type ) ); ?></strong>
						<br>
						<code><?php echo esc_html( strtoupper( $change->object_type ) ); ?></code>
						<span class="cp-object-id">#<?php echo esc_html( $change->object_id ); ?></span>
					</td>

					<!-- Reason -->
					<td>
						<?php echo esc_html( $change->reason ); ?>
					</td>

					<!-- Diff -->
					<td>
						<details>
							<summary class="cp-diff-toggle">
								<?php _e( 'View change snapshot', 'changeproof' ); ?>
							</summary>

							<div class="cp-timeline-diff">
								<div class="cp-diff-block">
									<strong><?php _e( 'Before', 'changeproof' ); ?></strong>
									<div class="cp-diff-excerpt">
										<?php echo cp_get_data_excerpt( $change->before_data ); ?>
									</div>
								</div>

								<div class="cp-diff-block">
									<strong><?php _e( 'After', 'changeproof' ); ?></strong>
									<div class="cp-diff-excerpt">
										<?php echo cp_get_data_excerpt( $change->after_data ); ?>
									</div>
								</div>
							</div>
						</details>
					</td>

				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
