import { render } from 'react-dom';
import Injector from 'lib/Injector';
import TileFieldComponent from './components/TileFieldComponent';
import React from 'react';

/**
 * This file creates the react tile component upon an entwine match. 
 * The data is provided via data-* attributes 
 */

Injector.ready(() => {
	jQuery.entwine('ss', ($) => {

		// in the future when we ditch entwine, we should be able to ditch jquery too
		$('.tilefield__react-container').entwine({
			onmatch() {

				let tiletypes = JSON.parse(this[0].getAttribute('data-avalible-types'));
				let addurl = this[0].getAttribute('data-addurl');
				let list = JSON.parse(this[0].getAttribute('data-list'));
				let editurl = this[0].getAttribute('data-editurl');
				let deleteurl = this[0].getAttribute('data-deleteurl');
				let disabled = this[0].getAttribute('data-disabled');
				let name = this[0].getAttribute('data-name');
				let rows = this[0].getAttribute('data-rows');
				let rowsenabled = this[0].getAttribute('data-rows-enabled');

				render(<TileFieldComponent 
							tiletypes={tiletypes}
							addurl={addurl} 
							editurl={editurl} 
							deleteurl={deleteurl}
							list={list}
							disabled={disabled}
							name={name}
							rows={rows}
							rowsenabled={rowsenabled}
							/>,
						this[0]);

				this._super();
			}
		});
	});// end ss jquery
});


Injector.transform(
	'tile-feild',
	(updater) => {
		updater.component(
			'TileField',
			TileFieldComponent
		);
	}
);