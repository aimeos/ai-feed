<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */

$enc = $this->encoder();


/** admin/jqadm/feed/fields
 * List of feed columns that should be displayed in the list view
 *
 * Changes the list of feed columns shown by default in the feed list view.
 * The columns can be changed by the editor as required within the administration
 * interface.
 *
 * The names of the columns are in fact the search keys defined by the managers,
 * e.g. "feed.id" for the feed ID.
 *
 * @param array List of field names, i.e. search keys
 * @since 2026.01
 */
$default = ['feed.status', 'feed.type', 'feed.label', 'feed.languageid', 'feed.currencyid'];
$default = $this->config( 'admin/jqadm/feed/fields', $default );
$fields = $this->session( 'aimeos/admin/jqadm/feed/fields', $default );

$searchParams = $params = $this->get( 'pageParams', [] );
$searchParams['page']['start'] = 0;

$searchAttributes = map( $this->get( 'filterAttributes', [] ) )->filter( function( $item ) {
	return $item->isPublic();
} )->call( 'toArray' )->each( function( &$val ) {
	$val = $this->translate( 'admin/code', $val['label'] ?? '' );
} )->all();

$operators = map( $this->get( 'filterOperators/compare', [] ) )->flip()->map( function( $val, $key ) {
	return $this->translate( 'admin/code', $key );
} )->all();

$columnList = [
	'feed.id'         => $this->translate( 'admin', 'ID' ),
	'feed.status'     => $this->translate( 'admin', 'Status' ),
	'feed.type'       => $this->translate( 'admin', 'Type' ),
	'feed.label'      => $this->translate( 'admin', 'Label' ),
	'feed.languageid' => $this->translate( 'admin', 'Language' ),
	'feed.currencyid' => $this->translate( 'admin', 'Currency' ),
	'feed.stock'      => $this->translate( 'admin', 'In-stock only' ),
	'feed.ctime'      => $this->translate( 'admin', 'Created' ),
	'feed.mtime'      => $this->translate( 'admin', 'Modified' ),
	'feed.editor'     => $this->translate( 'admin', 'Editor' ),
];

?>
<?php $this->block()->start( 'jqadm_content' ) ?>

<?= $this->partial( $this->config( 'admin/jqadm/partial/navsearch', 'navsearch' ) ) ?>
<?= $this->partial( $this->config( 'admin/jqadm/partial/columns', 'columns' ) ) ?>


<div class="list-view"
	data-domain="feed"
	data-siteid="<?= $enc->attr( $this->site()->siteid() ) ?>"
	data-filter="<?= $enc->attr( $this->session( 'aimeos/admin/jqadm/feed/filter', new \stdClass ) ) ?>"
	data-items="<?= $enc->attr( $this->get( 'items', map() )->call( 'toArray', [true] )->all() ) ?>">

	<nav class="main-navbar">

		<span class="navbar-brand">
			<?= $enc->html( $this->translate( 'admin', 'Exports' ) ) ?>
			<span class="navbar-secondary">(<?= $enc->html( $this->site()->label() ) ?>)</span>
		</span>

		<div class="btn icon act-search" v-on:click="search = true"
			title="<?= $enc->attr( $this->translate( 'admin', 'Show search form' ) ) ?>"
			aria-label="<?= $enc->attr( $this->translate( 'admin', 'Show search form' ) ) ?>">
		</div>
	</nav>

	<nav-search v-bind:show="search" v-on:close="search = false"
		v-bind:url="`<?= $enc->js( $this->link( 'admin/jqadm/url/search', map( $searchParams )->except( 'filter' )->all() ) ) ?>`"
		v-bind:filter="<?= $enc->attr( (object) $this->session( 'aimeos/admin/jqadm/feed/filter', new \stdClass ) ) ?>"
		v-bind:operators="<?= $enc->attr( $operators ) ?>"
		v-bind:name="`<?= $enc->js( $this->formparam( ['filter', '_key_', '0'] ) ) ?>`"
		v-bind:attributes="<?= $enc->attr( $searchAttributes ) ?>">
	</nav-search>

	<?= $this->partial(
			$this->config( 'admin/jqadm/partial/pagination', 'pagination' ),
			['pageParams' => $params, 'pos' => 'top', 'total' => $this->get( 'total' ),
			'page' => $this->session( 'aimeos/admin/jqadm/feed/page', [] )]
		);
	?>

	<form ref="form" class="list list-feed" method="POST"
		action="<?= $enc->attr( $this->link( 'admin/jqadm/url/search', $searchParams ) ) ?>">

		<?= $this->csrf()->formfield() ?>

		<column-select tabindex="<?= $this->get( 'tabindex', 1 ) ?>"
			name="<?= $enc->attr( $this->formparam( ['fields', ''] ) ) ?>"
			v-bind:titles="<?= $enc->attr( $columnList ) ?>"
			v-bind:fields="<?= $enc->attr( $fields ) ?>"
			v-bind:show="columns"
			v-on:close="columns = false">
		</column-select>

		<div class="table-responsive">
			<table class="list-items table table-hover table-striped">
				<thead class="list-header">
					<tr>
						<th class="select">
							<button class="btn icon-menu" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'
								aria-expanded="false" title="<?= $enc->attr( $this->translate( 'admin', 'Menu' ) ) ?>">
							</button>
							<ul class="dropdown-menu">
								<li>
									<a class="btn" v-on:click.prevent="batch = true" href="#" tabindex="1">
										<?= $enc->html( $this->translate( 'admin', 'Edit' ) ) ?>
									</a>
								</li>
								<li>
									<a class="btn" v-on:click.prevent="askDelete(null, $event)" tabindex="1"
										href="<?= $enc->attr( $this->link( 'admin/jqadm/url/delete', $params ) ) ?>">
										<?= $enc->html( $this->translate( 'admin', 'Delete' ) ) ?>
									</a>
								</li>
							</ul>
						</th>

						<?= $this->partial(
								$this->config( 'admin/jqadm/partial/listhead', 'listhead' ),
								['fields' => $fields, 'params' => $params, 'data' => $columnList, 'sort' => $this->session( 'aimeos/admin/jqadm/feed/sort' )]
							);
						?>

						<th class="actions">
							<a class="btn icon act-add" tabindex="1"
								href="<?= $enc->attr( $this->link( 'admin/jqadm/url/create', $params ) ) ?>"
								title="<?= $enc->attr( $this->translate( 'admin', 'Insert new entry (Ctrl+I)' ) ) ?>"
								aria-label="<?= $enc->attr( $this->translate( 'admin', 'Add' ) ) ?>">
							</a>

							<a class="btn act-columns icon" href="#" tabindex="<?= $this->get( 'tabindex', 1 ) ?>"
								title="<?= $enc->attr( $this->translate( 'admin', 'Columns' ) ) ?>"
								v-on:click.prevent.stop="columns = true">
							</a>
						</th>
					</tr>
				</thead>
				<tbody>

					<?= $this->partial(
						$this->config( 'admin/jqadm/partial/listsearch', 'listsearch' ), [
							'fields' => array_merge( $fields, ['select'] ), 'filter' => $this->session( 'aimeos/admin/jqadm/feed/filter', [] ),
							'data' => [
								'feed.id' => ['op' => '=='],
								'feed.status' => ['op' => '==', 'type' => 'select', 'val' => [
									'1' => $this->translate( 'mshop/code', 'status:1' ),
									'0' => $this->translate( 'mshop/code', 'status:0' ),
									'-1' => $this->translate( 'mshop/code', 'status:-1' ),
									'-2' => $this->translate( 'mshop/code', 'status:-2' ),
								]],
								'feed.type'       => [],
								'feed.label'      => [],
								'feed.languageid' => [],
								'feed.currencyid' => [],
								'feed.stock'      => ['op' => '==', 'type' => 'select', 'val' => [
									'1' => $this->translate( 'admin', 'Yes' ),
									'0' => $this->translate( 'admin', 'No' ),
								]],
								'feed.ctime'  => ['op' => '-', 'type' => 'datetime-local'],
								'feed.mtime'  => ['op' => '-', 'type' => 'datetime-local'],
								'feed.editor' => [],
							]
						] );
					?>

					<tr class="batch" v-bind:class="{show: batch}" v-show="batch">
						<td colspan="<?= count( $fields ) + 2 ?>">
							<div class="batch-header">
								<div class="intro">
									<span class="name"><?= $enc->html( $this->translate( 'admin', 'Bulk edit' ) ) ?></span>
									<span class="count">{{ selected }} <?= $enc->html( $this->translate( 'admin', 'selected' ) ) ?></span>
								</div>
								<a class="btn btn-secondary" href="#" v-on:click.prevent="batch = false">
									<?= $enc->html( $this->translate( 'admin', 'Close' ) ) ?>
								</a>
							</div>
							<div class="card">
								<div class="card-header">
									<span><?= $enc->html( $this->translate( 'admin', 'Basic' ) ) ?></span>
									<button class="btn btn-primary" formaction="<?= $enc->attr( $this->link( 'admin/jqadm/url/batch', ['resource' => 'feed'] ) ) ?>">
										<?= $enc->html( $this->translate( 'admin', 'Save' ) ) ?>
									</button>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-xl-6">
											<div class="row">
												<div class="col-1">
													<input id="batch-feed-status" class="form-check-input" type="checkbox" v-on:click="setState('item/feed.status')">
												</div>
												<label class="col-4 form-control-label" for="batch-feed-status">
													<?= $enc->html( $this->translate( 'admin', 'Status' ) ) ?>
												</label>
												<div class="col-7">
													<select class="form-select" v-bind:disabled="state('item/feed.status')"
														name="<?= $enc->attr( $this->formparam( ['item', 'feed.status'] ) ) ?>">
														<option value=""></option>
														<option value="1"><?= $enc->html( $this->translate( 'mshop/code', 'status:1' ) ) ?></option>
														<option value="0"><?= $enc->html( $this->translate( 'mshop/code', 'status:0' ) ) ?></option>
														<option value="-1"><?= $enc->html( $this->translate( 'mshop/code', 'status:-1' ) ) ?></option>
														<option value="-2"><?= $enc->html( $this->translate( 'mshop/code', 'status:-2' ) ) ?></option>
													</select>
												</div>
											</div>
											<div class="row">
												<div class="col-1">
													<input id="batch-feed-type" class="form-check-input" type="checkbox" v-on:click="setState('item/feed.type')">
												</div>
												<label class="col-4 form-control-label" for="batch-feed-type">
													<?= $enc->html( $this->translate( 'admin', 'Type' ) ) ?>
												</label>
												<div class="col-7">
													<select class="form-select" v-bind:disabled="state('item/feed.type')"
														name="<?= $enc->attr( $this->formparam( ['item', 'feed.type'] ) ) ?>">
														<option value=""></option>
														<?php foreach( $this->get( 'itemExportTypes', [] ) as $type ) : ?>
															<option value="<?= $enc->attr( $type ) ?>"><?= $enc->html( $type ) ?></option>
														<?php endforeach ?>
													</select>
												</div>
											</div>
										</div>
										<div class="col-xl-6">
											<div class="row">
												<div class="col-1">
													<input id="batch-feed-stock" class="form-check-input" type="checkbox" v-on:click="setState('item/feed.stock')">
												</div>
												<label class="col-4 form-control-label" for="batch-feed-stock">
													<?= $enc->html( $this->translate( 'admin', 'In-stock only' ) ) ?>
												</label>
												<div class="col-7">
													<select class="form-select" v-bind:disabled="state('item/feed.stock')"
														name="<?= $enc->attr( $this->formparam( ['item', 'feed.stock'] ) ) ?>">
														<option value=""></option>
														<option value="1"><?= $enc->html( $this->translate( 'admin', 'Yes' ) ) ?></option>
														<option value="0"><?= $enc->html( $this->translate( 'admin', 'No' ) ) ?></option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="batch-footer">
								<a class="btn btn-secondary" href="#" v-on:click.prevent="batch = false">
									<?= $enc->html( $this->translate( 'admin', 'Close' ) ) ?>
								</a>
								<button class="btn btn-primary" formaction="<?= $enc->attr( $this->link( 'admin/jqadm/url/batch', ['resource' => 'feed'] ) ) ?>">
									<?= $enc->html( $this->translate( 'admin', 'Save' ) ) ?>
								</button>
							</div>
						</td>
					</tr>

					<?php foreach( $this->get( 'items', [] ) as $id => $item ) : ?>
						<?php $url = $enc->attr( $this->link( 'admin/jqadm/url/get', ['id' => $id] + $params ) ) ?>
						<tr class="list-item <?= $this->site()->mismatch( $item->getSiteId() ) ?>" data-label="<?= $enc->attr( $item->getLabel() ) ?>">
							<td class="select">
								<input class="form-check-input" type="checkbox" tabindex="1"
									name="<?= $enc->attr( $this->formparam( ['id', ''] ) ) ?>"
									value="<?= $enc->attr( $item->getId() ) ?>"
									v-on:click="toggle(`<?= $enc->js( $id ) ?>`)"
									v-bind:checked="checked(`<?= $enc->js( $id ) ?>`)"
									v-bind:disabled="readonly(`<?= $enc->js( $id ) ?>`)">
							</td>
							<?php if( in_array( 'feed.id', $fields ) ) : ?>
								<td class="feed-id"><a class="items-field" href="<?= $url ?>"><?= $enc->html( $item->getId() ) ?></a></td>
							<?php endif ?>
							<?php if( in_array( 'feed.status', $fields ) ) : ?>
								<td class="feed-status"><a class="items-field" href="<?= $url ?>"><div class="icon status-<?= $enc->attr( $item->getStatus() ) ?>"></div></a></td>
							<?php endif ?>
							<?php if( in_array( 'feed.type', $fields ) ) : ?>
								<td class="feed-type"><a class="items-field" href="<?= $url ?>"><?= $enc->html( $item->getType() ) ?></a></td>
							<?php endif ?>
							<?php if( in_array( 'feed.label', $fields ) ) : ?>
								<td class="feed-label"><a class="items-field" href="<?= $url ?>" tabindex="1"><?= $enc->html( $item->getLabel() ) ?></a></td>
							<?php endif ?>
							<?php if( in_array( 'feed.languageid', $fields ) ) : ?>
								<td class="feed-languageid"><a class="items-field" href="<?= $url ?>"><?= $enc->html( $item->getLanguageId() ) ?></a></td>
							<?php endif ?>
							<?php if( in_array( 'feed.currencyid', $fields ) ) : ?>
								<td class="feed-currencyid"><a class="items-field" href="<?= $url ?>"><?= $enc->html( $item->getCurrencyId() ) ?></a></td>
							<?php endif ?>
							<?php if( in_array( 'feed.stock', $fields ) ) : ?>
								<td class="feed-stock"><a class="items-field" href="<?= $url ?>"><div class="icon status-<?= $enc->attr( $item->getStock() ? 1 : 0 ) ?>"></div></a></td>
							<?php endif ?>
							<?php if( in_array( 'feed.ctime', $fields ) ) : ?>
								<td class="feed-ctime"><a class="items-field" href="<?= $url ?>"><?= $enc->html( $item->getTimeCreated() ) ?></a></td>
							<?php endif ?>
							<?php if( in_array( 'feed.mtime', $fields ) ) : ?>
								<td class="feed-mtime"><a class="items-field" href="<?= $url ?>"><?= $enc->html( $item->getTimeModified() ) ?></a></td>
							<?php endif ?>
							<?php if( in_array( 'feed.editor', $fields ) ) : ?>
								<td class="feed-editor"><a class="items-field" href="<?= $url ?>"><?= $enc->html( $item->editor() ) ?></a></td>
							<?php endif ?>

							<td class="actions">
								<a class="btn act-copy icon" tabindex="1"
									href="<?= $enc->attr( $this->link( 'admin/jqadm/url/copy', ['id' => $id] + $params ) ) ?>"
									title="<?= $enc->attr( $this->translate( 'admin', 'Copy this entry' ) ) ?>"
									aria-label="<?= $enc->attr( $this->translate( 'admin', 'Copy' ) ) ?>">
								</a>
								<?php if( !$this->site()->readonly( $item->getSiteId() ) ) : ?>
									<a class="btn act-delete icon" tabindex="1"
										v-on:click.prevent.stop="askDelete(`<?= $enc->js( $id ) ?>`, $event)"
										href="<?= $enc->attr( $this->link( 'admin/jqadm/url/delete', ['id' => $id] + $params ) ) ?>"
										title="<?= $enc->attr( $this->translate( 'admin', 'Delete this entry' ) ) ?>"
										aria-label="<?= $enc->attr( $this->translate( 'admin', 'Delete' ) ) ?>">
									</a>
								<?php endif ?>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>

		<?php if( $this->get( 'items', map() )->isEmpty() ) : ?>
			<?= $enc->html( sprintf( $this->translate( 'admin', 'No entries found' ) ) ) ?>
		<?php endif ?>
	</form>

	<?= $this->partial(
			$this->config( 'admin/jqadm/partial/pagination', 'pagination' ),
			['pageParams' => $params, 'pos' => 'bottom', 'total' => $this->get( 'total' ),
			'page' => $this->session( 'aimeos/admin/jqadm/feed/page', [] )]
		);
	?>

	<confirm-delete v-bind:items="unconfirmed" v-bind:show="dialog"
		v-on:close="confirmDelete(false)" v-on:confirm="confirmDelete(true)">
	</confirm-delete>

</div>
<?php $this->block()->stop() ?>

<?= $this->render( $this->config( 'admin/jqadm/template/page', 'page' ) ) ?>
