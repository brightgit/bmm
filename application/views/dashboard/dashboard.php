
<div class="dashboard">
	
	<div class="full-widget">
		<div class="collumn">
			<span class="operator">/</span>
			<span class="number"><?php echo $dashboard->total_delivered ?></span>
			<span class="label">Entregues</span>			
		</div>
		
		<div class="collumn">
			<span class="operator">=</span>
			<span class="number"><?php echo $dashboard->total_sent ?></span>
			<span class="label">Enviados</span>
		</div>
		
		<div class="collumn collumn-result">
			<span class="number"><?php echo number_format((($dashboard->total_delivered * 100) / $dashboard->total_sent), 2) ?>%</span>
			<span class="label">Taxa de entrega</span>
		</div>
		
		<div class="clear"></div>
		<hr class="data-split" />
		
		<table class="compact" width="100%">
			<tr>
				<td>
					<span class="number"><?php echo number_format(($dashboard->total_sent / 12), 2) ?></span>
					<span class="label">Envios / mês</span>
				</td>
				<td>
					<span class="number"><?php echo $dashboard->subscribers_per_month ?></span>
					<span class="label">Subscritores / mês</span>
				</td>
				<td>
					<span class="number"><?php echo $dashboard->hard_bounces_count ?></span>
					<span class="label">Devolvidos</span>
				</td>
				<td>
					<span class="number"><?php echo $dashboard->total_exclusion_requests_time_interval[0]->total ?></span>
					<span class="label">Pedidos remoção</span>
				</td>
			</tr>
		</table>
		
		
	</div>
	
	<!-- envios -->
	<table width="100%;" class="table-widgets">
		<tr>
			<td>
	<div class="widget widget-orange flip-container">
			
		<div class="flipper widget-wrap">					
			<div class="front">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-cog"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-envelope"></i> Envios último <?php echo $dashboard->time_interval_labels[$_SESSION["envios_pie"]] ?></h2>
				<div id="morris-graph-demo-1" style="height:170px;">
					<?php 
						$percent_abertas = number_format(($dashboard->opened_last_time_interval * 100 / $dashboard->delivered_last_time_interval),2);
						$percent_entregues = number_format(($dashboard->delivered_last_time_interval - $dashboard->opened_last_time_interval - $dashboard->bounced_last_time_interval) * 100 / $dashboard->delivered_last_time_interval, 2);
						$percent_bounces = number_format(($dashboard->bounced_last_time_interval * 100) / $dashboard->delivered_last_time_interval, 2);
					?>
					<input type="hidden" name="pie_graph_delivered_last_time_interval" value="<?php echo $percent_entregues ?>" />
					<input type="hidden" name="pie_graph_opened_last_time_interval" value="<?php echo $percent_abertas ?>" />
					<input type="hidden" name="pie_graph_bounced_last_time_interval" value="<?php echo $percent_bounces ?>" />
				</div>
			</div>
			<div class="back">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-check"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-envelope"></i> Envios último <?php echo $dashboard->time_interval_labels[$_SESSION["envios_pie"]] ?></h2>
				<div class="back-settings" <?php echo ( $_SESSION["user"]->is_admin )?' style="padding-top:0;"':'' ?>>
					<div class="back-settings" <?php echo ( $_SESSION["user"]->is_admin )?' style="padding-top:0;"':'' ?>>
						<form name="envios_pie" action="?mod=dashboard" method="post">
							<label>Seleccionar período</label>
							<select name="time_period[envios_pie]">
								<option>Escolha um período</option>
								<option value="trimester">Último trimestre</option>
								<option value="semester">Último semestre</option>						
								<option value="year">Último ano</option>
							</select>
							<?php if ($_SESSION["user"]->is_admin): ?>
								<label>Utilizador</label>
								<select name="time_period[envios_pie_users]">
								<?php foreach ($dashboard->all_users as $key => $value): ?>
									<?php $ids[] = $value["id"]; ?>
									<option <?php echo ( $_SESSION["envios_pie_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo $value["id"] ?>"><?php echo $value["name"] ?></option>
								<?php endforeach ?>
									<option <?php echo ( $_SESSION["envios_pie_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo implode(",", $ids); ?>">Todos</option>

								</select>
								
							<?php endif ?>
							<input type="submit" name="submit" value="Alterar" class="btn btn-primary" />
						</form>
					</div>
					<!--label>Apresentar:</label>
					<span class="setting-inline"><input type="checkbox" checked="checked" /> Entregues</span>
					<span class="setting-inline"><input type="checkbox" checked="checked" /> Lidos</span>
					<span class="setting-inline"><input type="checkbox" checked="checked" /> Devolvidos</span>
					<span class="setting-inline"><input type="checkbox" /> Cliques</span-->
				</div>
			</div>
		</div>
		
	</div>
	<!-- /envios -->
	</td>
	
	<td>
	
	<!-- envios -->
	<div class="widget widget-blue widget-center flip-container">
			
		<div class="flipper widget-wrap">					
			<div class="front">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-cog"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-user"></i> Subscritores último <?php echo $dashboard->time_interval_labels[$_SESSION["subscritores_bars"]] ?></h2>
				<div id="morris-graph-demo-2" style="height:220px;">
					<?php foreach($dashboard->subscribers_by_interval as $month => $total): ?>
					<input type="hidden" class="subscriber_month_totals" name="month_<?php echo $month ?>" value="<?php echo $total ?>" />
					<?php endforeach; ?>
				</div>
			</div>
			<div class="back">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-check"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-user"></i> Subscritores último <?php echo $dashboard->time_interval_labels[$_SESSION["subscritores_bars"]]; ?></h2>
				<div class="back-settings" <?php echo ( $_SESSION["user"]->is_admin )?' style="padding-top:0;"':'' ?>>
					<form name="subscritores_bars" action="?mod=dashboard" method="post">
						<label>Seleccionar período</label>
						<select name="time_period[subscritores_bars]">
							<option>Escolha um período</option>
							<option value="trimester">Último trimestre</option>
							<option value="semester">Último semestre</option>						
							<option value="year">Último ano</option>
						</select>
						<?php if ($_SESSION["user"]->is_admin): ?>
							<label>Utilizador</label>
							<select name="time_period[subscritores_bars_users]">
							<?php foreach ($dashboard->all_users as $key => $value): ?>
								<?php $ids[] = $value["id"]; ?>
								<option <?php echo ( $_SESSION["subscritores_bars_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo $value["id"] ?>"><?php echo $value["name"] ?></option>
							<?php endforeach ?>
								<option <?php echo ( $_SESSION["subscritores_bars_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo implode(",", $ids); ?>">Todos</option>

							</select>
							
						<?php endif ?>
						<input type="submit" name="submit" value="Alterar" class="btn btn-primary" />
					</form>
				</div>
			</div>
		</div>
		
	</div>
	<!-- /envios -->
	</td>
	
	
	
	<td>
	<!-- dados gerais -->
	<div class="widget widget-green flip-container">
			
		<div class="flipper widget-wrap">					
			<div class="front">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-cog"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-signal"></i> Totais último <?php echo $dashboard->time_interval_labels[$_SESSION["totais_stats"]] ?></h2>

				<ul class="general-data-list">
					<!-- subscribers -->
					<li>
						<strong><?php echo $dashboard->total_subscribers_last_time_interval[0]->total; ?></strong> Subscritores 
						<?php if(($dashboard->total_subscribers_last_time_interval[1]->total != 0) && $dashboard->total_subscribers_last_time_interval[0]->total > $dashboard->total_subscribers_last_time_interval[1]->total): ?>
						<span class="compare compare-positive"><?php echo (($dashboard->total_subscribers_last_time_interval[0]->total * 100) / $dashboard->total_subscribers_last_time_interval[1]->total) -100 ?>% <span class="label label-success"><i class="icon-white icon-chevron-up"></span></i></span>
						<?php elseif(($dashboard->total_subscribers_last_time_interval[1]->total != 0) && $dashboard->total_subscribers_last_time_interval[0]->total < $dashboard->total_subscribers_last_time_interval[1]->total): ?>
						<span class="compare compare-negative"><?php echo number_format(100 - (($dashboard->total_subscribers_last_time_interval[0]->total * 100) / $dashboard->total_subscribers_last_time_interval[1]->total), 2) ?>% <span class="label label-important"><i class="icon-white icon-chevron-down"></span></i></span>
						<?php else: ?>
						<span class="compare compare-negative">0% <span class="label label-warning"><i class="icon-white icon-minus"></span></i></span>
						<?php endif; ?>
					</li>
					<!-- newsletters -->
					<li>
						<strong><?php echo $dashboard->total_newsletter_sends_time_interval[0]->total ?></strong> Newsletters enviadas 
						<?php if(($dashboard->total_newsletter_sends_time_interval[1]->total != 0) && $dashboard->total_newsletter_sends_time_interval[0]->total > $dashboard->total_newsletter_sends_time_interval[1]->total): ?>
						<span class="compare compare-positive"><?php echo (($dashboard->total_newsletter_sends_time_interval[0]->total * 100) / $dashboard->total_newsletter_sends_time_interval[1]->total) -100 ?>% <span class="label label-success"><i class="icon-white icon-chevron-up"></span></i></span>
						<?php elseif(($dashboard->total_newsletter_sends_time_interval[1]->total != 0) && $dashboard->total_newsletter_sends_time_interval[0]->total < $dashboard->total_newsletter_sends_time_interval[1]->total): ?>
						<span class="compare compare-negative"><?php echo number_format(100 - (($dashboard->total_newsletter_sends_time_interval[0]->total * 100) / $dashboard->total_newsletter_sends_time_interval[1]->total), 2) ?>% <span class="label label-important"><i class="icon-white icon-chevron-down"></span></i></span>
						<?php else: ?>
						<span class="compare compare-negative"><?php echo $dashboard->total_newsletter_sends_time_interval[0]->total; ?> <span class="label label-success"><i class="icon-white icon-chevron-up"></span></i></span>
						<?php endif; ?>
					</li>

					<!-- pedidos remoção -->
					<li>
						<strong><?php echo $dashboard->total_exclusion_requests_time_interval[0]->total ?></strong> Pedido(s) de remoção 
						<?php if($dashboard->total_exclusion_requests_time_interval[0]->total > $dashboard->total_exclusion_requests_time_interval[1]->total && $dashboard->total_exclusion_requests_time_interval[1]->total != 0): ?>
						<span class="compare compare-positive"><?php echo (($dashboard->total_exclusion_requests_time_interval[0]->total * 100) / $dashboard->total_exclusion_requests_time_interval[1]->total) - 100 ?>% <span class="label label-success"><i class="icon-white icon-chevron-up"></span></i></span>
						<?php elseif($dashboard->total_exclusion_requests_time_interval[1]->total == 0): ?>
						<span class="compare compare-positive"> <?php echo $dashboard->total_exclusion_requests_time_interval[0]->total ?> <span class="label label-success"><i class="icon-white icon-chevron-up"></span></i></span>
						<?php elseif($dashboard->total_exclusion_requests_time_interval[0]->total < $dashboard->total_exclusion_requests_time_interval[1]->total): ?>
						<span class="compare compare-negative"><?php echo number_format(100 - (($dashboard->total_exclusion_requests_time_interval[0]->total * 100) / $dashboard->total_exclusion_requests_time_interval[1]->total), 2) ?>% <span class="label label-important"><i class="icon-white icon-chevron-down"></span></i></span>
						<?php else: ?>
						<span class="compare compare-negative">0% <span class="label label-warning"><i class="icon-white icon-minus"></span></i></span>
						<?php endif; ?>
					</li>
				</ul>
			</div>
			<div class="back">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-check"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-signal"></i> Totais último <?php echo $dashboard->time_interval_labels[$_SESSION["totais_stats"]] ?></h2>
				<div class="back-settings" <?php echo ( $_SESSION["user"]->is_admin )?' style="padding-top:0;"':'' ?>>
					<form name="totais_stats" action="?mod=dashboard" method="post">
						<label>Seleccionar período</label>
						<select name="time_period[totais_stats]">
							<option>Escolha um período</option>
							<option value="trimester">Último trimestre</option>
							<option value="semester">Último semestre</option>						
							<option value="year">Último ano</option>
						</select>
						<?php if ($_SESSION["user"]->is_admin): ?>
							<label>Utilizador</label>
							<select name="time_period[totais_stats_users]">
							<?php foreach ($dashboard->all_users as $key => $value): ?>
								<?php $ids[] = $value["id"]; ?>
								<option <?php echo ( $_SESSION["totais_stats_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo $value["id"] ?>"><?php echo $value["name"] ?></option>
							<?php endforeach ?>
								<option <?php echo ( $_SESSION["totais_stats_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo implode(",", $ids); ?>">Todos</option>

							</select>
							
						<?php endif ?>
						<input type="submit" name="submit" value="Alterar" class="btn btn-primary" />
					</form>
				</div>
			</div>
		</div>
		
	</div>
	<!-- /dados gerais -->
	</td>
	</tr>
	</table>
	
	<div class="clear"></div>
		
</div>