<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */

$selected = function( $key, $code ) {
	return ( $key == $code ? 'selected="selected"' : '' );
};

$enc = $this->encoder();

$params   = $this->get( 'pageParams', [] );
$locales  = $this->get( 'itemLocales', map() );
$attrTypes = $this->get( 'itemAttrTypes', [] );

$languages  = $locales->getLanguageId()->unique()->filter()->sort()->all();
$currencies = $locales->getCurrencyId()->unique()->filter()->sort()->all();

$exportTypes   = $this->get( 'itemExportTypes', [] );
$data          = $this->get( 'itemData', [] );

$includeCatData  = $data['category']['include'] ?? [];
$excludeCatData  = $data['category']['exclude'] ?? [];
$includeProdData = $data['product']['include'] ?? [];
$excludeProdData = $data['product']['exclude'] ?? [];

$catKeys  = ['feed.lists.id', 'feed.lists.siteid', 'feed.lists.type', 'catalog.label', 'catalog.code', 'catalog.id'];
$prodKeys = ['feed.lists.id', 'feed.lists.siteid', 'feed.lists.type', 'product.label', 'product.code', 'product.id'];

?>
<?php $this->block()->start( 'jqadm_content' ) ?>

<form class="item item-feed form-horizontal container-fluid" method="POST" enctype="multipart/form-data"
	action="<?= $enc->attr( $this->link( 'admin/jqadm/url/save', $params ) ) ?>">
	<input id="item-id" type="hidden" name="<?= $enc->attr( $this->formparam( ['item', 'feed.id'] ) ) ?>"
		value="<?= $enc->attr( $this->get( 'itemData/feed.id' ) ) ?>">
	<input id="item-next" type="hidden" name="<?= $enc->attr( $this->formparam( ['next'] ) ) ?>" value="get">
	<?= $this->csrf()->formfield() ?>

	<nav class="main-navbar">
		<h1 class="navbar-brand">
			<span class="navbar-title"><?= $enc->html( $this->translate( 'admin', 'Feed' ) ) ?></span>
			<span class="navbar-id"><?= $enc->html( $this->get( 'itemData/feed.id' ) ) ?></span>
			<span class="navbar-label"><?= $enc->html( $this->get( 'itemData/feed.label' ) ?: $this->translate( 'admin', 'New' ) ) ?></span>
			<span class="navbar-site"><?= $enc->html( $this->site()->match( $this->get( 'itemData/feed.siteid' ) ) ) ?></span>
		</h1>
		<div class="item-actions">
			<?= $this->partial( $this->config( 'admin/jqadm/partial/itemactions', 'itemactions' ), ['params' => $params] ) ?>
		</div>
	</nav>

	<div class="row item-container">

		<div class="col-xl-3 item-navbar">
			<div class="navbar-content" v-bind:class="{show: show}">
				<ul class="nav nav-tabs flex-xl-column flex-wrap d-flex box" role="tablist">
					<li class="nav-item basic">
						<a class="nav-link active" href="#basic" v-on:click="url(`basic`)"
							data-bs-toggle="tab" role="tab" aria-expanded="true" aria-controls="basic">
							<?= $enc->html( $this->translate( 'admin', 'Basic' ) ) ?>
						</a>
					</li>
					<li class="nav-item category">
						<a class="nav-link" href="#category" v-on:click="url(`category`)"
							data-bs-toggle="tab" role="tab" tabindex="2">
							<?= $enc->html( $this->translate( 'admin', 'Categories' ) ) ?>
						</a>
					</li>
					<li class="nav-item product">
						<a class="nav-link" href="#product" v-on:click="url(`product`)"
							data-bs-toggle="tab" role="tab" tabindex="3">
							<?= $enc->html( $this->translate( 'admin', 'Products' ) ) ?>
						</a>
					</li>
					<li class="nav-item attributes">
						<a class="nav-link" href="#attributes" v-on:click="url(`attributes`)"
							data-bs-toggle="tab" role="tab" tabindex="4">
							<?= $enc->html( $this->translate( 'admin', 'Attributes' ) ) ?>
						</a>
					</li>

					<?php foreach( array_values( $this->get( 'itemSubparts', [] ) ) as $idx => $subpart ) : ?>
						<li class="nav-item <?= $enc->attr( $subpart ) ?>">
							<a class="nav-link" href="#<?= $enc->attr( $subpart ) ?>" v-on:click="url(`<?= $enc->js( $subpart ) ?>`)"
								data-bs-toggle="tab" role="tab" tabindex="<?= $idx + 5 ?>">
								<?= $enc->html( $this->translate( 'admin', $subpart ) ) ?>
							</a>
						</li>
					<?php endforeach ?>
				</ul>

				<div class="item-meta text-muted">
					<small>
						<?= $enc->html( $this->translate( 'admin', 'Modified' ) ) ?>:
						<span class="meta-value"><?= $enc->html( $this->get( 'itemData/feed.mtime' ) ) ?></span>
					</small>
					<small>
						<?= $enc->html( $this->translate( 'admin', 'Created' ) ) ?>:
						<span class="meta-value"><?= $enc->html( $this->get( 'itemData/feed.ctime' ) ) ?></span>
					</small>
					<small>
						<?= $enc->html( $this->translate( 'admin', 'Editor' ) ) ?>:
						<span class="meta-value"><?= $enc->html( $this->get( 'itemData/feed.editor' ) ) ?></span>
					</small>
				</div>

				<div class="icon more" v-bind:class="{less: show}" v-on:click="toggle()"></div>
			</div>
		</div>

		<div class="col-xl-9 item-content tab-content">

			<!-- Basic tab -->
			<div id="basic" class="item-basic tab-pane fade show active" role="tabpanel" aria-labelledby="basic"
				data-data="<?= $enc->attr( $this->get( 'itemData', new stdClass() ) ) ?>"
				data-siteid="<?= $enc->attr( $this->site()->siteid() ) ?>">

				<div class="box <?= $this->site()->mismatch( $this->get( 'itemData/feed.siteid' ) ) ?>">
					<div class="row">
						<div class="col-xl-6 block">

							<div class="form-group row mandatory">
								<label class="col-sm-4 form-control-label"><?= $enc->html( $this->translate( 'admin', 'Status' ) ) ?></label>
								<div class="col-sm-8">
									<select class="form-select item-status" required="required" tabindex="1"
										name="<?= $enc->attr( $this->formparam( ['item', 'feed.status'] ) ) ?>"
										v-bind:readonly="!can('change')">
										<option value="">
											<?= $enc->html( $this->translate( 'admin', 'Please select' ) ) ?>
										</option>
										<option value="1" <?= $selected( $this->get( 'itemData/feed.status', 1 ), 1 ) ?>>
											<?= $enc->html( $this->translate( 'mshop/code', 'status:1' ) ) ?>
										</option>
										<option value="0" <?= $selected( $this->get( 'itemData/feed.status', 1 ), 0 ) ?>>
											<?= $enc->html( $this->translate( 'mshop/code', 'status:0' ) ) ?>
										</option>
										<option value="-1" <?= $selected( $this->get( 'itemData/feed.status', 1 ), -1 ) ?>>
											<?= $enc->html( $this->translate( 'mshop/code', 'status:-1' ) ) ?>
										</option>
										<option value="-2" <?= $selected( $this->get( 'itemData/feed.status', 1 ), -2 ) ?>>
											<?= $enc->html( $this->translate( 'mshop/code', 'status:-2' ) ) ?>
										</option>
									</select>
								</div>
							</div>

							<div class="form-group row mandatory">
								<label class="col-sm-4 form-control-label help"><?= $enc->html( $this->translate( 'admin', 'Export type' ) ) ?></label>
								<div class="col-sm-8">
									<select class="form-select item-type" required="required" tabindex="1"
										name="<?= $enc->attr( $this->formparam( ['item', 'feed.type'] ) ) ?>"
										v-bind:readonly="!can('change')"
										v-model="item['feed.type']">
										<option value="">
											<?= $enc->html( $this->translate( 'admin', 'Please select' ) ) ?>
										</option>
										<?php foreach( $exportTypes as $type ) : ?>
											<option value="<?= $enc->attr( $type ) ?>" <?= $selected( $this->get( 'itemData/feed.type' ), $type ) ?>>
												<?= $enc->html( ucfirst( $type ) ) ?>
											</option>
										<?php endforeach ?>
									</select>
								</div>
								<div class="col-sm-12 form-text text-muted help-text">
									<?= $enc->html( $this->translate( 'admin', 'The export format used to generate this product feed' ) ) ?>
								</div>
							</div>

							<div class="form-group row mandatory">
								<label class="col-sm-4 form-control-label help"><?= $enc->html( $this->translate( 'admin', 'Label' ) ) ?></label>
								<div class="col-sm-8">
									<input class="form-control item-label" type="text" required="required" tabindex="1"
										name="<?= $enc->attr( $this->formparam( ['item', 'feed.label'] ) ) ?>"
										placeholder="<?= $enc->attr( $this->translate( 'admin', 'Internal name (required)' ) ) ?>"
										value="<?= $enc->attr( $this->get( 'itemData/feed.label' ) ) ?>"
										v-bind:readonly="!can('change')">
								</div>
								<div class="col-sm-12 form-text text-muted help-text">
									<?= $enc->html( $this->translate( 'admin', 'Descriptive name for this feed configuration' ) ) ?>
								</div>
							</div>

							<div class="form-group row mandatory">
								<label class="col-sm-4 form-control-label help"><?= $enc->html( $this->translate( 'admin', 'Language' ) ) ?></label>
								<div class="col-sm-8">
									<select class="form-select item-languageid" required="required" tabindex="1"
										name="<?= $enc->attr( $this->formparam( ['item', 'feed.languageid'] ) ) ?>"
										v-bind:readonly="!can('change')">
										<option value="">
											<?= $enc->html( $this->translate( 'admin', 'Please select' ) ) ?>
										</option>
										<?php foreach( $languages as $langid ) : ?>
											<option value="<?= $enc->attr( $langid ) ?>" <?= $selected( $this->get( 'itemData/feed.languageid' ), $langid ) ?>>
												<?= $enc->html( $this->translate( 'language', $langid ) ) ?> (<?= $enc->html( $langid ) ?>)
											</option>
										<?php endforeach ?>
									</select>
								</div>
								<div class="col-sm-12 form-text text-muted help-text">
									<?= $enc->html( $this->translate( 'admin', 'Language of the product data to be exported in this feed' ) ) ?>
								</div>
							</div>

							<div class="form-group row mandatory">
								<label class="col-sm-4 form-control-label help"><?= $enc->html( $this->translate( 'admin', 'Currency' ) ) ?></label>
								<div class="col-sm-8">
									<select class="form-select item-currencyid" required="required" tabindex="1"
										name="<?= $enc->attr( $this->formparam( ['item', 'feed.currencyid'] ) ) ?>"
										v-bind:readonly="!can('change')">
										<option value="">
											<?= $enc->html( $this->translate( 'admin', 'Please select' ) ) ?>
										</option>
										<?php foreach( $currencies as $currencyid ) : ?>
											<option value="<?= $enc->attr( $currencyid ) ?>" <?= $selected( $this->get( 'itemData/feed.currencyid' ), $currencyid ) ?>>
												<?= $enc->html( $this->translate( 'currency', $currencyid ) ) ?> (<?= $enc->html( $currencyid ) ?>)
											</option>
										<?php endforeach ?>
									</select>
								</div>
								<div class="col-sm-12 form-text text-muted help-text">
									<?= $enc->html( $this->translate( 'admin', 'Currency of the prices to be exported in this feed' ) ) ?>
								</div>
							</div>

							<div class="form-group row optional">
								<label class="col-sm-4 form-control-label help"><?= $enc->html( $this->translate( 'admin', 'In-stock only' ) ) ?></label>
								<div class="col-sm-8">
									<div class="form-check">
										<input class="form-check-input item-stock" type="checkbox" tabindex="1"
											name="<?= $enc->attr( $this->formparam( ['item', 'feed.stock'] ) ) ?>"
											value="1"
											<?= ( $this->get( 'itemData/feed.stock' ) ? 'checked="checked"' : '' ) ?>
											v-bind:disabled="!can('change')">
										<label class="form-check-label">
											<?= $enc->html( $this->translate( 'admin', 'Export only in-stock products' ) ) ?>
										</label>
									</div>
								</div>
								<div class="col-sm-12 form-text text-muted help-text">
									<?= $enc->html( $this->translate( 'admin', 'When enabled, only products with available stock are included in the feed' ) ) ?>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>

			<!-- Categories tab -->
			<div id="category" class="item-category tab-pane fade" role="tabpanel" aria-labelledby="category">
				<div class="row">

					<!-- Included categories -->
					<div id="feed-category-include" class="col-xl-6 catalog"
						data-data="<?= $enc->attr( array_values( $includeCatData ) ) ?>"
						data-keys="<?= $enc->attr( $catKeys ) ?>"
						data-siteid="<?= $this->site()->siteid() ?>"
						data-listtype="include">

						<div class="box">
							<table class="category-list table table-default">

								<thead>
									<tr>
										<th>
											<?= $enc->html( $this->translate( 'admin', 'Included categories' ) ) ?>
										</th>
										<th class="actions">
											<div class="btn act-add icon" tabindex="<?= $this->get( 'tabindex', 1 ) ?>"
												title="<?= $enc->attr( $this->translate( 'admin', 'Insert new entry (Ctrl+I)' ) ) ?>"
												v-on:click="add()">
											</div>
										</th>
									</tr>
								</thead>

								<tbody>

									<tr v-for="(item, idx) in items" v-bind:key="idx" v-bind:class="{'mismatch': !can('match', idx)}">
										<td v-bind:class="item['css'] || ''">
											<input class="item-listtype" type="hidden" v-model="item['feed.lists.type']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'category', 'include', '_idx_', 'feed.lists.type'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-listid" type="hidden" v-model="item['feed.lists.id']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'category', 'include', '_idx_', 'feed.lists.id'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-id" type="hidden" v-model="item['catalog.id']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'category', 'include', '_idx_', 'catalog.id'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-label" type="hidden" v-model="item['catalog.label']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'category', 'include', '_idx_', 'catalog.label'] ) ) ?>`.replace( '_idx_', idx )">

											<Multiselect class="item-id form-control"
												placeholder="<?= $enc->attr( $this->translate( 'admin', 'Enter category ID, code or label' ) ) ?>"
												value-prop="catalog.id"
												track-by="catalog.id"
												label="catalog.label"
												@open="function(select) {return select.refreshOptions()}"
												@input="use(idx, $event)"
												:value="item"
												:title="title(idx)"
												:disabled="!can('change', idx)"
												:options="async function(query) {return await fetch(query, idx)}"
												:resolve-on-load="false"
												:filter-results="false"
												:can-deselect="false"
												:allow-absent="true"
												:searchable="true"
												:can-clear="false"
												:required="true"
												:min-chars="1"
												:object="true"
												:delay="300"
											></Multiselect>
										</td>
										<td class="actions">
											<div v-if="can('delete', idx)" class="btn act-delete icon" tabindex="<?= $this->get( 'tabindex', 1 ) ?>"
												title="<?= $enc->attr( $this->translate( 'admin', 'Delete this entry' ) ) ?>"
												v-on:click.stop="remove(idx)">
											</div>
										</td>
									</tr>

								</tbody>

							</table>
						</div>
					</div>

					<!-- Excluded categories -->
					<div id="feed-category-exclude" class="col-xl-6 catalog"
						data-data="<?= $enc->attr( array_values( $excludeCatData ) ) ?>"
						data-keys="<?= $enc->attr( $catKeys ) ?>"
						data-siteid="<?= $this->site()->siteid() ?>"
						data-listtype="exclude">

						<div class="box">
							<table class="category-list table table-default">

								<thead>
									<tr>
										<th>
											<?= $enc->html( $this->translate( 'admin', 'Excluded categories' ) ) ?>
										</th>
										<th class="actions">
											<div class="btn act-add icon" tabindex="<?= $this->get( 'tabindex', 1 ) ?>"
												title="<?= $enc->attr( $this->translate( 'admin', 'Insert new entry (Ctrl+I)' ) ) ?>"
												v-on:click="add()">
											</div>
										</th>
									</tr>
								</thead>

								<tbody>

									<tr v-for="(item, idx) in items" v-bind:key="idx" v-bind:class="{'mismatch': !can('match', idx)}">
										<td v-bind:class="item['css'] || ''">
											<input class="item-listtype" type="hidden" v-model="item['feed.lists.type']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'category', 'exclude', '_idx_', 'feed.lists.type'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-listid" type="hidden" v-model="item['feed.lists.id']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'category', 'exclude', '_idx_', 'feed.lists.id'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-id" type="hidden" v-model="item['catalog.id']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'category', 'exclude', '_idx_', 'catalog.id'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-label" type="hidden" v-model="item['catalog.label']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'category', 'exclude', '_idx_', 'catalog.label'] ) ) ?>`.replace( '_idx_', idx )">

											<Multiselect class="item-id form-control"
												placeholder="<?= $enc->attr( $this->translate( 'admin', 'Enter category ID, code or label' ) ) ?>"
												value-prop="catalog.id"
												track-by="catalog.id"
												label="catalog.label"
												@open="function(select) {return select.refreshOptions()}"
												@input="use(idx, $event)"
												:value="item"
												:title="title(idx)"
												:disabled="!can('change', idx)"
												:options="async function(query) {return await fetch(query, idx)}"
												:resolve-on-load="false"
												:filter-results="false"
												:can-deselect="false"
												:allow-absent="true"
												:searchable="true"
												:can-clear="false"
												:required="true"
												:min-chars="1"
												:object="true"
												:delay="300"
											></Multiselect>
										</td>
										<td class="actions">
											<div v-if="can('delete', idx)" class="btn act-delete icon" tabindex="<?= $this->get( 'tabindex', 1 ) ?>"
												title="<?= $enc->attr( $this->translate( 'admin', 'Delete this entry' ) ) ?>"
												v-on:click.stop="remove(idx)">
											</div>
										</td>
									</tr>

								</tbody>

							</table>
						</div>
					</div>

				</div>
			</div>

			<!-- Products tab -->
			<div id="product" class="item-product tab-pane fade" role="tabpanel" aria-labelledby="product">
				<div class="row">

					<!-- Included products -->
					<div id="feed-product-include" class="col-xl-6 product"
						data-data="<?= $enc->attr( array_values( $includeProdData ) ) ?>"
						data-keys="<?= $enc->attr( $prodKeys ) ?>"
						data-siteid="<?= $this->site()->siteid() ?>"
						data-listtype="include">

						<div class="box">
							<table class="product-list table table-default">

								<thead>
									<tr>
										<th>
											<?= $enc->html( $this->translate( 'admin', 'Included products' ) ) ?>
										</th>
										<th class="actions">
											<div class="btn act-add icon" tabindex="<?= $this->get( 'tabindex', 1 ) ?>"
												title="<?= $enc->attr( $this->translate( 'admin', 'Insert new entry (Ctrl+I)' ) ) ?>"
												v-on:click="add()">
											</div>
										</th>
									</tr>
								</thead>

								<tbody>

									<tr v-for="(item, idx) in items" v-bind:key="idx" v-bind:class="{'mismatch': !can('match', idx)}">
										<td v-bind:class="item['css'] || ''">
											<input class="item-listtype" type="hidden" v-model="item['feed.lists.type']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'product', 'include', '_idx_', 'feed.lists.type'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-listid" type="hidden" v-model="item['feed.lists.id']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'product', 'include', '_idx_', 'feed.lists.id'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-id" type="hidden" v-model="item['product.id']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'product', 'include', '_idx_', 'product.id'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-label" type="hidden" v-model="item['product.label']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'product', 'include', '_idx_', 'product.label'] ) ) ?>`.replace( '_idx_', idx )">

											<Multiselect class="item-id form-control"
												placeholder="<?= $enc->attr( $this->translate( 'admin', 'Enter product ID, code or label' ) ) ?>"
												value-prop="product.id"
												track-by="product.id"
												label="product.label"
												@open="function(select) {return select.refreshOptions()}"
												@input="use(idx, $event)"
												:value="item"
												:title="title(idx)"
												:disabled="!can('change', idx)"
												:options="async function(query) {return await fetch(query, idx)}"
												:resolve-on-load="false"
												:filter-results="false"
												:can-deselect="false"
												:allow-absent="true"
												:searchable="true"
												:can-clear="false"
												:required="true"
												:min-chars="1"
												:object="true"
												:delay="300"
											></Multiselect>
										</td>
										<td class="actions">
											<div v-if="can('delete', idx)" class="btn act-delete icon" tabindex="<?= $this->get( 'tabindex', 1 ) ?>"
												title="<?= $enc->attr( $this->translate( 'admin', 'Delete this entry' ) ) ?>"
												v-on:click.stop="remove(idx)">
											</div>
										</td>
									</tr>

								</tbody>

							</table>
						</div>
					</div>

					<!-- Excluded products -->
					<div id="feed-product-exclude" class="col-xl-6 product"
						data-data="<?= $enc->attr( array_values( $excludeProdData ) ) ?>"
						data-keys="<?= $enc->attr( $prodKeys ) ?>"
						data-siteid="<?= $this->site()->siteid() ?>"
						data-listtype="exclude">

						<div class="box">
							<table class="product-list table table-default">

								<thead>
									<tr>
										<th>
											<?= $enc->html( $this->translate( 'admin', 'Excluded products' ) ) ?>
										</th>
										<th class="actions">
											<div class="btn act-add icon" tabindex="<?= $this->get( 'tabindex', 1 ) ?>"
												title="<?= $enc->attr( $this->translate( 'admin', 'Insert new entry (Ctrl+I)' ) ) ?>"
												v-on:click="add()">
											</div>
										</th>
									</tr>
								</thead>

								<tbody>

									<tr v-for="(item, idx) in items" v-bind:key="idx" v-bind:class="{'mismatch': !can('match', idx)}">
										<td v-bind:class="item['css'] || ''">
											<input class="item-listtype" type="hidden" v-model="item['feed.lists.type']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'product', 'exclude', '_idx_', 'feed.lists.type'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-listid" type="hidden" v-model="item['feed.lists.id']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'product', 'exclude', '_idx_', 'feed.lists.id'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-id" type="hidden" v-model="item['product.id']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'product', 'exclude', '_idx_', 'product.id'] ) ) ?>`.replace( '_idx_', idx )">

											<input class="item-label" type="hidden" v-model="item['product.label']"
												v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'product', 'exclude', '_idx_', 'product.label'] ) ) ?>`.replace( '_idx_', idx )">

											<Multiselect class="item-id form-control"
												placeholder="<?= $enc->attr( $this->translate( 'admin', 'Enter product ID, code or label' ) ) ?>"
												value-prop="product.id"
												track-by="product.id"
												label="product.label"
												@open="function(select) {return select.refreshOptions()}"
												@input="use(idx, $event)"
												:value="item"
												:title="title(idx)"
												:disabled="!can('change', idx)"
												:options="async function(query) {return await fetch(query, idx)}"
												:resolve-on-load="false"
												:filter-results="false"
												:can-deselect="false"
												:allow-absent="true"
												:searchable="true"
												:can-clear="false"
												:required="true"
												:min-chars="1"
												:object="true"
												:delay="300"
											></Multiselect>
										</td>
										<td class="actions">
											<div v-if="can('delete', idx)" class="btn act-delete icon" tabindex="<?= $this->get( 'tabindex', 1 ) ?>"
												title="<?= $enc->attr( $this->translate( 'admin', 'Delete this entry' ) ) ?>"
												v-on:click.stop="remove(idx)">
											</div>
										</td>
									</tr>

								</tbody>

							</table>
						</div>
					</div>

				</div>
			</div>

			<!-- Attribute mapping tab -->
			<div id="attributes" class="item-attributes tab-pane fade" role="tabpanel" aria-labelledby="attributes"
				data-data="<?= $enc->attr( $this->get( 'itemData', new stdClass() ) ) ?>"
				data-attrtypes="<?= $enc->attr( $attrTypes ) ?>"
				data-siteid="<?= $enc->attr( $this->site()->siteid() ) ?>">
				<div class="box <?= $this->site()->mismatch( $this->get( 'itemData/feed.siteid' ) ) ?>">
					<div class="row">
						<div class="col-xl-12 block">

							<config-table tabindex="1"
								v-bind:keys="JSON.parse(attrtypes)[item['feed.type']] || []"
								v-bind:name="`<?= $enc->js( $this->formparam( ['item', 'config', 'attributes', '_pos_', '_key_'] ) ) ?>`"
								v-bind:items="(item['config'] || {})['attributes'] || []"
								v-on:update:items="if(!item['config']) item['config'] = {}; item['config']['attributes'] = $event"
								v-bind:readonly="!can('change')"
								v-bind:i18n="{
									value: `<?= $enc->js( $this->translate( 'admin', 'Attribute type' ) ) ?>`,
									option: `<?= $enc->js( $this->translate( 'admin', 'Feed field' ) ) ?>`,
									help: `<?= $enc->js( $this->translate( 'admin', 'Map a feed field name to the Aimeos product attribute type containing the value' ) ) ?>`,
									insert: `<?= $enc->js( $this->translate( 'admin', 'Insert new entry (Ctrl+I)' ) ) ?>`,
									delete: `<?= $enc->js( $this->translate( 'admin', 'Delete this entry' ) ) ?>`,
								}">
								<table class="item-config table">
									<thead>
										<tr>
											<th class="config-row-key"><span class="help"><?= $enc->html( $this->translate( 'admin', 'Feed field' ) ) ?></span></th>
											<th class="config-row-value"><?= $enc->html( $this->translate( 'admin', 'Attribute type' ) ) ?></th>
											<th class="actions"><div class="btn act-add icon"></div></th>
										</tr>
									</thead>
								</table>
							</config-table>

						</div>
					</div>
				</div>
			</div>

			<?= $this->get( 'itemBody' ) ?>

		</div>

		<div class="item-actions">
			<?= $this->partial( $this->config( 'admin/jqadm/partial/itemactions', 'itemactions' ), ['params' => $params] ) ?>
		</div>
	</div>
</form>

<?php $this->block()->stop() ?>


<?= $this->render( $this->config( 'admin/jqadm/template/page', 'page' ) ) ?>
