<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e( 'Changeproof Timeline', 'changeproof' ); ?></h1>
	<hr class="wp-header-end">

	<table class="wp-list-table widefat fixed striped table-view-list">
		<thead>
			<tr>
				<th style="width: 15%"><?php _e( 'Date/Time', 'changeproof' ); ?></th>
				<th style="width: 15%"><?php _e( 'User', 'changeproof' ); ?></th>
				<th style="width: 15%"><?php _e( 'Object', 'changeproof' ); ?></th>
				<th style="width: 25%"><?php _e( 'Reason/Intent', 'changeproof' ); ?></th>
				<th><?php _e( 'Changes', 'changeproof' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $changes ) ) : ?>
				<tr>
					<td colspan="5"><?php _e( 'No changes recorded yet.', 'changeproof' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $changes as $change ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $change->created_at ); ?></strong><br>
							<?php if ( $change->investigation_id ) : ?>
								<span class="badge cp-inv-badge" style="background:#2271b1; color:#fff; padding:2px 6px; border-radius:3px; font-size:10px;">
									ID: #<?php echo esc_html( $change->investigation_id ); ?>
								</span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $change->display_name ? $change->display_name : 'System/Unknown' ); ?></td>
						<td>
							<code><?php echo esc_html( strtoupper( $change->object_type ) ); ?></code><br>
							ID: <?php echo esc_html( $change->object_id ); ?>
						</td>
						<td>
							<strong><?php echo esc_html( $change->reason ); ?></strong>
						</td>
						<td>
							<details>
								<summary style="cursor:pointer; color:#2271b1;"><?php _e( 'View Diff Excerpt', 'changeproof' ); ?></summary>
								<div class="cp-timeline-diff" style="margin-top:10px; font-size:12px;">
									<div style="margin-bottom:5px;">
										<strong><?php _e( 'Before:', 'changeproof' ); ?></strong>
										<div class="cp-diff-excerpt"><?php echo cp_get_data_excerpt( $change->before_data ); ?></div>
									</div>
									<div>
										<strong><?php _e( 'After:', 'changeproof' ); ?></strong>
										<div class="cp-diff-excerpt"><?php echo cp_get_data_excerpt( $change->after_data ); ?></div>
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