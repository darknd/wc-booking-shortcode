<?php
function shortcode_list()
{
	global $current_user, $wpdb;

	if ($current_user->display_name == 'admin')
	{
		$results = $wpdb->get_results("SELECT ID,post_parent FROM wp_posts
					       WHERE post_status = 'paid' AND post_type = 'wc_booking'");
		echo "<table>
		        <thead>
              <tr>
                <th>First name</th>
                <th>Last name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Start date</th>
                <th>Paid</th>
              </tr>
            </thead>";
		echo "<tbody>";
		echo "<tr>";
		foreach ($results as $item)
		{
			$personal_data = $wpdb->get_results("
			SELECT max(CASE WHEN meta_key = '_billing_first_name'
			AND post_id = $item->post_parent THEN meta_value END) as first_name,
			max(CASE WHEN meta_key = '_billing_last_name' AND post_id = $item->post_parent THEN meta_value END)
			as last_name,max(CASE WHEN meta_key = '_billing_email' AND post_id = $item->post_parent THEN
			meta_value END) as email,max(CASE WHEN meta_key = '_billing_phone' AND
			post_id = $item->post_parent THEN meta_value END) as phone 
			FROM wp_postmeta WHERE post_id = $item->post_parent");
			
			foreach ($personal_data as $personal)
			{
				echo "<td>".$personal->first_name."</td>";
				echo "<td>".$personal->last_name."</td>";
				echo "<td>".$personal->email."</td>";
				echo "<td>".$personal->phone."</td>";
				$booking_id = $wpdb->get_results("
				SELECT order_item_id FROM wp_woocommerce_order_itemmeta WHERE meta_value = $item->ID ");
				
				foreach ($booking_id as $id)
				{
					$day_booking = $wpdb->get_results("
					SELECT max(CASE WHEN meta_key = '_line_total' 
					AND order_item_id = $id->order_item_id THEN meta_value END) as paid,
					max(CASE WHEN meta_key = 'Día de reserva' AND
					order_item_id = $id->order_item_id THEN meta_value END) as booking
					FROM wp_woocommerce_order_itemmeta WHERE order_item_id = $id->order_item_id ");
					
					foreach($day_booking as $day)
					{
						echo "<td>".$day->booking."</td>";
						echo "<td>".$day->paid."€</td>";
					}
				}
			}
		echo "</tr>";
		}
		echo "</table>";
	}else{
		echo "<script>
		       window.location.replace('http://www.yourwebsite.com');
			</script>";
	}
}

add_shortcode('booking_list', 'shortcode_list');

?>
