<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function convertifier_add_dashboard_widgets() {

	add_action('admin_head', 'convertifier_admin_styles');
    wp_add_dashboard_widget( 'dashboard_widget', 'Convertifier At a Glance', 'convertifier_dashboard_widget' );
    add_action('admin_footer', 'convertifier_admin_js');
}
add_action( 'wp_dashboard_setup', 'convertifier_add_dashboard_widgets' );

function convertifier_admin_styles()
{
	?>
<style type="text/css">
.lines-block-main{font-size: 0;margin-top: -11px;}
.line-chart-block{border-right: 1px solid #e1e1e1;border-radius: 0;padding: 20px 22px;margin-bottom: 5px;background-color: #fff;position: relative;width: 33%;display: inline-block;box-sizing:border-box;}
.line-chart-block:last-child{border-right: 0;}
.line-chart-block .graph-head{width: 55%;float: left;}
.line-chart-block .canvas-block{width: 45%;float: left;margin-bottom: 20px;height: 50px;}
.line-chart-block .canvas-block canvas{max-height: 100%;}
.line-chart-block.h-60 canvas{height: 40px !important;width: 148px !important;}
.line-chart-block.h-60{padding: 20px 7px;}
.line-chart-block.h-60 > span {font-size: 11px;}
.line-chart-block.h-60 .stats big{font-size: 28px;}
.line-chart-block.h-60 li{border:0;width: auto;margin-right: 30px;}
.line-chart-block.h-60 .number{font-size:14px;color:#333;}
.line-chart-block.h-60 .period{font-size:14px;color:#a4a4a4;text-transform: none;}
.line-chart-block.h-60 .stats i{width: 15px;height: 10px;}
.line-chart-block.h-60 .stats i:before{top: 0;background-position: -3px 0;}
.line-chart-block > span{font-size: 14px;display: block;color: #333;font-weight: 600;margin-bottom: 5px;}
.line-chart-block .canvas-b{width: 162px;height: 50px;}
.line-chart-block.h-60 .stats big {font-size: 32px;}
.line-chart-block .stats big{font-size: 26px;color: #303030;display: inline-block;font-weight: 400;line-height: 28px;vertical-align: middle;}
.line-chart-block .stats i{font-style: normal;display: inline-block;margin-left: 15px;font-size: 12px;color: #00a65a;padding-left: 18px;position: relative;vertical-align: middle;}
.line-chart-block .stats i:before{position: absolute;content: '';background: url(/wp-content/plugins/convertifier_push/images/icn-stats.png) no-repeat;height: 10px;width: 15px;left: 0;top: 4px;}
.line-chart-block .stats.down i{color: #e6442e;}
.line-chart-block .stats.down i:before{background-position: right 0;}
.line-chart-block ul{padding: 0;margin: 10px 0 0 0;}
.line-chart-block ul li{display: inline-block;width: 50%;text-align: center;border-right: 1px solid #e1e1e1;}
.line-chart-block ul li.today{border:0;float: right;margin-right: 12px;}
.line-chart-block ul .period{color: #2d98ee;font-size: 12px;display: block;text-transform: capitalize;}
.line-chart-block ul .number{color: #303030;font-size: 16px;display: block;}
.line-chart-block.h-60 > span:before{font-family: dashicons;font-weight: 400;font-size: 18px;display: inline-block;vertical-align: top;margin: 0 5px 0 0;}
.line-chart-block.h-60 > span.total_subscriber:before{content: '\f307';}
.line-chart-block.h-60 > span.notification_sent:before{content: '\f177';}
.line-chart-block.h-60 > span.notification_clicked:before{content: '\f504';}
#convertifier-stats .welcome-panel-content {margin-left: 0;}
.postbox .inside.convertifier_inside{padding: 0 2px 12px;}
</style>
<?php
}

function convertifier_admin_js()
{
	if (isConvertifierSetupComplete) {
	?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js"></script>
	<script type="text/javascript">
	function insert_lable(label_name){
		var label_array = [];
		for(var i = 0; i<= label_name.length; i++){
			var j = i;
			label_array.push(j);
		}
		return label_array;
	}
	jQuery(document).ready(function($) {

		$.ajax({
		    type: "GET",
		    url: "<?=CONVERTIFIER_API_URL?>website/stats/all",
		    xhrFields: { withCredentials: true },
		    headers: {'Authorization': 'Basic <?=CONVERTIFIER_AUTH_KEY?>'},
		    crossDomain: true,
		    //async: false
		}).done(function( data ) {
			$('#convertifier-stats #subscribers_total').html(data.subscribers_total);
			$('#convertifier-stats #subscribers_monthly').html(data.subscribers_this_month);
			$('#convertifier-stats #subscribers_today').html(data.subscribers_today);
			$('#convertifier-stats #n_sent_total').html(data.notifications_sent_total);
			$('#convertifier-stats #n_sent_monthly').html(data.notifications_sent_this_month);
			$('#convertifier-stats #n_sent_today').html(data.notifications_sent_today);
			$('#convertifier-stats #n_clicked_total').html(data.notifications_clicked_total);
			$('#convertifier-stats #n_clicked_month').html(data.notifications_clicked_this_month);
			$('#convertifier-stats #n_clicked_today').html(data.notifications_clicked_today);
			$('#convertifier-stats #subscribers_stat').addClass(data.subscribers_stat);
			$('#convertifier-stats #sent_stat').addClass(data.notifications_sent_stat);
			$('#convertifier-stats #clicked_stat').addClass(data.notifications_clicked_stat);
			Chart.defaults.global.elements.line.borderColor = [ 'rgba(201,108,94,1)' ];
			Chart.defaults.global.elements.line.backgroundColor = ['rgba(255, 99, 132, 0)'];
			Chart.defaults.global.elements.line.borderWidth = 2;
			//Chart.defaults.global.elements.line.pointRadius = 0;
			//line chart global options
			var line_chart_options = {
		        scales: { yAxes: [{ display: false }], xAxes: [{ display: false }],},
		        responsive: true,
		    	legend: { display: false, }
			}
			new Chart($(".Line-chart-subscriber"), {
			    type: 'line',
			    data: {
			        labels: insert_lable(data.subscribers_data),
			        datasets: [{
			            label: '',
			            data: data.subscribers_data,
			            pointRadius: 0,
			        }]
			    },
			    options: line_chart_options
			});
			new Chart($(".Line-chart-notification"), {
			    type: 'line',
			    data: {
			        labels: insert_lable(data.notifications_sent_data),
			        datasets: [{
			            label: '',
			            data: data.notifications_sent_data,
			            pointRadius: 0,
			        }]
			    },
			    options: line_chart_options
			});
			new Chart($(".Line-chart-clicked"), {
			    type: 'line',
			    data: {
			        labels: insert_lable(data.notifications_clicked_data),
			        datasets: [{
			            label: '',
			            data: data.notifications_clicked_data,
			            pointRadius: 0,
			        }]
			    },
			    options: line_chart_options
			});
		}).fail(function(data) {
			$('#welcome-panel').after("<div class='error notice is-dismissible'><p><strong>There is an Error communicating with Convertifier API. Please ensure your APP Key and API Token are correct. </strong></p></div>");
			console.log(data);
		});
		$('#convertifier-stats').parent().addClass('convertifier_inside');
	});
</script>
	<?php
	}
}
function convertifier_dashboard_widget($post, $callback_args) {

	if (isConvertifierSetupComplete) {
?>
	<div id="convertifier-stats">
		<div class="welcome-panel-content">
			<div class="welcome-panel-column-container">				
				<div class="lines-block-main">
					<div class="line-chart-block h-60">
						<div class="icon"></div>
						<span class="total_subscriber">Total Subscriber</span>
						<div id="subscribers_stat" class="stats">
							<big><span id="subscribers_total"></span></big>
							<i></i>
						</div>
						<div class="canvas-b">
							<canvas class="Line-chart-subscriber" width="162" height="81" style="width: 162px; height: 81px;"></canvas>
						</div>
						<ul>
							<li class="month"><span class="number" id="subscribers_monthly"></span><span class="period">month</span></li>
							<li class="today"><span class="number" id="subscribers_today"></span><span class="period">today</span></li>
						</ul>
					</div>
					<div class="line-chart-block h-60">
						<div class="icon"></div>
						<span class="notification_sent">Notification Sent</span>
						<div id="sent_stat" class="stats">
							<big><span id="n_sent_total"></span></big>
							<i></i>
						</div>
						<div class="canvas-b">
							<canvas class="Line-chart-notification" width="162" height="81" style="width: 162px; height: 81px;"></canvas>
						</div>
						<ul>
							<li class="month"><span class="number" id="n_sent_monthly"></span><span class="period">month</span></li>
							<li class="today"><span class="number" id="n_sent_today"></span><span class="period">today</span></li>
						</ul>
					</div>
					<div class="line-chart-block h-60">
						<div class="icon"></div>
						<span class="notification_clicked">Notification Clicked</span>
						<div id="clicked_stat" class="stats">
							<big><span id="n_clicked_total"></span></big>
							<i></i>
						</div>
						<div class="canvas-b">
							<canvas class="Line-chart-clicked" width="162" height="81" style="width: 162px; height: 81px;"></canvas>
						</div>
						<ul>
							<li class="month"><span class="number" id="n_clicked_month"></span><span class="period">month</span></li>
							<li class="today"><span class="number" id="n_clicked_today"></span><span class="period">today</span></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php }
}