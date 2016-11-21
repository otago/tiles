
jQuery('.gridTileContainer').entwine({
	loadPanel: function () {
		this.redrawTabs();
		this._super();
	},
	onmatch: function () {
		this.redrawTabs();
		this._super();
	},
	onunmatch: function () {
		this._super();
	},
	buildinput: function () {
		var ul = jQuery(this).children('.gridster').children('ul');
		sarray = new Array();
		ul.find('li').each(function () {
			if (jQuery(this).hasClass('preview-holder'))
				return;
			sarray.push({
				'id': jQuery(this).attr('data-id'),
				'col': jQuery(this).attr('data-col'),
				'row': jQuery(this).attr('data-row')
			});
		});
		var input = jQuery(this).children('.gridster').children('input');
		input.val(JSON.stringify(sarray));
	},
	redrawTabs: function () {
		var _this = this;
		var _url = jQuery(this).children().children('input').data('url');


		// init the draggable gridfield
		var gridster = jQuery(_this).find("ul").gridster({
			widget_base_dimensions: [230, 210],
			widget_margins: [5, 5],
			autogrow_cols: true,
			max_cols: parseInt(jQuery(_this).find("ul").attr('data-maxcols'), 10),
			draggable: {
				handle: 'header',
				stop: function (event, ui) {
					_this.buildinput();
					var data = jQuery(_this).children('.gridster').children('input').val();
					var _loading = jQuery(_this).find('.gridsterloader');
					_loading.html('<img src="cms/images/network-save.gif"/>');

					jQuery.ajax(jQuery.extend({}, {
						headers: {"X-Pjax": 'CurrentField'},
						type: "POST",
						url: _url + '/saveTiles',
						dataType: 'html',
						data: {'order': data},
						success: function (data) {
							_loading.html('');
						},
						error: function (e) {
							_loading.html('');
						}
					}));
				}
			}
		}).data('gridster');

		// editing an item
		jQuery(_this).on('click', 'ul li header .btn-icon-pencil', function () {
			var _editli = jQuery(this).closest('li');
			var _editid = _editli.attr('data-id');
			jQuery('.cms-container').entwine('.ss').loadPanel(_url + '/editTile/' + _editid);
		});

		// delete an item 
		jQuery(_this).on('click', 'ul li header .btn-icon-delete', function () {
			if (confirm('Are you sure you want to delete this record?')) {
				var _deleteli = jQuery(this).closest('li');
				var _deleteid = _deleteli.attr('data-id');
				var _loading = jQuery(_this).find('.gridsterloader');
				_loading.html('<img src="cms/images/network-save.gif"/>');

				jQuery.ajax(jQuery.extend({}, {
					headers: {"X-Pjax": 'CurrentField'},
					type: "POST",
					url: _url + '/removeTile',
					data: {'id': _deleteid},
					success: function (data) {
						_loading.html('');
						gridster.remove_widget(_deleteli);
					},
					error: function (e) {
						_loading.html('');
					}
				}));
			}
		});

		// selecting a tile type
		jQuery(_this).find("select").on('change', function () {
			jQuery(_this).find("button").addClass('ui-state-disabled');
			if (jQuery(this).val() != "") {
				jQuery(_this).find("button").removeClass('ui-state-disabled');
			}
		});

		// upon the jquery ui creation
		jQuery(_this).find("button").addClass('ui-state-disabled');
		jQuery(_this).find('button').on("buttoncreate", function (event, ui) {
			jQuery(_this).find("button").addClass('ui-state-disabled');
		});


		// adding a new element
		jQuery(_this).find("button").on('click', function () {
			if (jQuery(this).hasClass('ui-state-disabled'))
				return false;
			var _loading = jQuery(_this).find('.gridsterloader');
			_loading.html('<img src="cms/images/network-save.gif"/>');

			var form = jQuery(this).closest('form').addClass('loading');
			var newtype = jQuery(_this).find('select').val();
			if (!newtype || newtype == "") {
				alert('cant find tile type');
				return false;
			}

			jQuery.ajax(jQuery.extend({}, {
				headers: {"X-Pjax": 'CurrentField'},
				type: "POST",
				url: _url + '/addTile',
				dataType: 'html',
				data: {'type': newtype},
				success: function (data) {
					form.removeClass('loading');
					_loading.html('');
					json_data = JSON.parse(data);
					attrs = 'data-id="' + json_data.ID + '" data-sizex' + json_data.sizex + " data-sizey=" + json_data.sizey;
					headr = '<header> <span class="ui-button-icon-primary ui-icon btn-icon-pencil"></span> |||';
					
					if (json_data.isversioned) {
						headr += '<span class="ui-button-icon-primary btn-icon-version">' + json_data.Version + '</span>';
					}
					headr += '</header>';

					headr += '<strong>' + newtype + '</strong><br/>';
					var y = 1;
					while (true) {
						if (gridster.is_empty(1, y)) {
							gridster.add_widget.apply(gridster, ['<li ' + attrs + '>' + headr + json_data.HTML + '</li>', json_data.sizex, json_data.sizey, 1, y]);
							break;
						}
						y++;
						if (y > 500) {// we went too far...
							gridster.add_widget.apply(gridster, ['<li ' + attrs + '>' + headr + json_data.HTML + '</li>', json_data.sizex, json_data.sizey]);
						}
					}
					_this.buildinput();
					// save the location
					jQuery.ajax(jQuery.extend({}, {
						headers: {"X-Pjax": 'CurrentField'},
						type: "POST",
						url: _url + '/saveTiles',
						dataType: 'html',
						data: {'order': jQuery(_this).children('.gridster').children('input').val()}
					}));
				},
				error: function (e) {
					form.removeClass('loading');
					_loading.html('');
				}
			}));
		});

	}
});