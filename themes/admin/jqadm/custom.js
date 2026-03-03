/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */



Aimeos.Feed = {

	init() {
		if(document.querySelector('.item-feed')) {
			this.components();
		}
	},


	components() {

		const components = [
			{
				name: 'feed/basic',
				el: '.item-feed .item-basic',
				mixins: [Aimeos.Feed.Basic.mixins()]
			},
			{
				name: 'feed/attributes',
				el: '.item-feed .item-attributes',
				mixins: [Aimeos.Feed.Attributes.mixins()]
			}
		]

		for(entry of document.querySelectorAll('.item-feed .item-category .catalog')) {
			components.push({
				name: entry.id.replace(/-/, '/'),
				el: '#' + entry.id,
				mixins: [Aimeos.Feed.Catalog.mixins()]
			})
		}

		for(entry of document.querySelectorAll('.item-feed .item-product .product')) {
			components.push({
				name: entry.id.replace(/-/, '/'),
				el: '#' + entry.id,
				mixins: [Aimeos.Feed.Product.mixins()]
			})
		}

		for(const component of components) {
			const node = document.querySelector(component.el);

			if(node) {
				Aimeos.apps[component.name] = Aimeos.app({
					mixins: component.mixins
				}, {...node.dataset || {}}).mount(node);
			}
		}
	}
};



Aimeos.Feed.Basic = {

	mixins() {
		return {
			props: {
				data: {type: String, required: true},
				siteid: {type: String, required: true}
			},
			data() {
				return {
					item: {},
				}
			},
			beforeMount() {
				this.Aimeos = Aimeos;
				this.item = JSON.parse(this.data);
			},
			methods: {
				can(action) {
					return Aimeos.can(action, this.item['feed.siteid'] || null, this.siteid)
				}
			}
		}
	}
};



Aimeos.Feed.Attributes = {

	mixins() {
		return {
			props: {
				data: {type: String, required: true},
				attrtypes: {type: String, default: '{}'},
				siteid: {type: String, required: true}
			},
			data() {
				return {
					item: {},
				}
			},
			beforeMount() {
				this.Aimeos = Aimeos;
				this.item = JSON.parse(this.data);
			},
			methods: {
				can(action) {
					return Aimeos.can(action, this.item['feed.siteid'] || null, this.siteid)
				}
			}
		}
	}
};



Aimeos.Feed.Catalog = {

	mixins() {
		return {
			props: {
				data: {type: String, required: true},
				keys: {type: String, required: true},
				siteid: {type: String, required: true},
				listtype: {type: String, required: true}
			},
			data() {
				return {
					items: [],
				}
			},
			beforeMount() {
				this.Aimeos = Aimeos;
				this.items = JSON.parse(this.data);
			},
			methods: {
				add(data) {

					let idx = (this.items || []).length;
					this.items[idx] = {};

					for(const key of (JSON.parse(this.keys) || [])) {
						this.items[idx][key] = (data && data[key] || '');
					}

					this.items[idx]['feed.lists.siteid'] = this.siteid;
					this.items[idx]['feed.lists.type'] = this.listtype;
				},


				can(action, idx) {
					return Aimeos.can(action, this.items[idx]['feed.lists.siteid'] || null, this.siteid)
				},


				fetch(input, idx) {
					const filter = {
						'&&': [
							{'>': {'catalog.status': 0}}
						]
					}

					if(input) {
						filter['&&'].push({
							'||': [
								{'=~': {'catalog.label': input}},
								{'=~': {'catalog.code': input}},
								{'==': {'catalog.id': input}}
							]
						});
					}

					return Aimeos.graphql(`query {
						searchCatalogs(filter: ` + JSON.stringify(JSON.stringify(filter)) + `, sort: ["catalog.label"]) {
							items {
								id
								code
								label
							}
						}
					  }
					`).then(result => {
						return (result?.searchCatalogs?.items || []).map(item => {
							return {'catalog.id': item.id, 'catalog.label': item.label + ' (' + item.code + ')'}
						})
					})
				},


				remove(idx) {
					this.items.splice(idx, 1);
				},


				title(idx) {
					if(this.items[idx]['feed.lists.ctime']) {
						return 'Site ID: ' + this.items[idx]['feed.lists.siteid'] + "\n"
							+ 'Editor: ' + this.items[idx]['feed.lists.editor'] + "\n"
							+ 'Created: ' + this.items[idx]['feed.lists.ctime'] + "\n"
							+ 'Modified: ' + this.items[idx]['feed.lists.mtime'];
					}
					return ''
				},


				use(idx, ev) {
					this.items[idx]['catalog.label'] = ev['catalog.label'];
					this.items[idx]['catalog.id'] = ev['catalog.id'];
				},
			}
		};
	}
};



Aimeos.Feed.Product = {

	mixins() {
		return {
			props: {
				data: {type: String, required: true},
				keys: {type: String, required: true},
				siteid: {type: String, required: true},
				listtype: {type: String, required: true}
			},
			data() {
				return {
					items: [],
				}
			},
			beforeMount() {
				this.Aimeos = Aimeos;
				this.items = JSON.parse(this.data);
			},
			methods: {
				add(data) {

					const idx = (this.items || []).length;
					this.items[idx] = {};

					for(const key of (JSON.parse(this.keys) || [])) {
						this.items[idx][key] = (data && data[key] || '');
					}

					this.items[idx]['feed.lists.siteid'] = this.siteid;
					this.items[idx]['feed.lists.type'] = this.listtype;
				},


				can(action, idx) {
					return Aimeos.can(action, this.items[idx]['feed.lists.siteid'] || null, this.siteid)
				},


				fetch(input, idx) {
					const filter = {
						'&&': [
							{'>': {'product.status': 0}}
						]
					}

					if(input) {
						filter['&&'].push({
							'||': [
								{'=~': {'product.label': input}},
								{'=~': {'product.code': input}},
								{'==': {'product.id': input}}
							]
						});
					}

					return Aimeos.graphql(`query {
						searchProducts(filter: ` + JSON.stringify(JSON.stringify(filter)) + `, sort: ["product.label"]) {
							items {
								id
								code
								label
							}
						}
					  }
					`).then(result => {
						return (result?.searchProducts?.items || []).map(item => {
							return {'product.id': item.id, 'product.label': item.label + ' (' + item.code + ')'}
						})
					})
				},


				remove(idx) {
					this.items.splice(idx, 1);
				},


				title(idx) {
					if(this.items[idx]['feed.lists.ctime']) {
						return 'Site ID: ' + this.items[idx]['feed.lists.siteid'] + "\n"
							+ 'Editor: ' + this.items[idx]['feed.lists.editor'] + "\n"
							+ 'Created: ' + this.items[idx]['feed.lists.ctime'] + "\n"
							+ 'Modified: ' + this.items[idx]['feed.lists.mtime'];
					}
					return ''
				},


				use(idx, ev) {
					this.items[idx]['product.label'] = ev['product.label'];
					this.items[idx]['product.id'] = ev['product.id'];
				},
			}
		};
	}
};



document.addEventListener("DOMContentLoaded", function() {
	Aimeos.Feed.init();
});
