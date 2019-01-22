import React, { Component } from 'react';
import PropTypes from 'prop-types';
import i18n from 'i18n';
import classnames from 'classnames';
import { inject, loadComponent } from 'lib/Injector';
import GridLayout from 'react-grid-layout';


/**
 * TileField renders a grid of tiles that allows drag and drop reordering and resizing
 */
class TileField extends Component {
    
    _container: ?HTMLInputElement;
    
	constructor(props) {
		super(props);
		this.onSelectChanged = this.onSelectChanged.bind(this);
		this.addTile = this.addTile.bind(this);
		this.onGridChange = this.onGridChange.bind(this);
		this.RemoveItem = this.RemoveItem.bind(this);
		this.onChangeWidth = this.onChangeWidth.bind(this);

		this.state = {
			isDirty: false,
			tiletypes: JSON.parse(this.props.AvalibleTypes),
			currentNewTile: '',
			value: JSON.parse(this.props.List),
			rows: this.props.Rows
		};
	}
    
    // used to navigate the DOM 
    getClosest ( elem, selector ) {
        // Element.matches() polyfill
        if (!Element.prototype.matches) {
            Element.prototype.matches =
                Element.prototype.matchesSelector ||
                Element.prototype.mozMatchesSelector ||
                Element.prototype.msMatchesSelector ||
                Element.prototype.oMatchesSelector ||
                Element.prototype.webkitMatchesSelector ||
                function(s) {
                    var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                        i = matches.length;
                    while (--i >= 0 && matches.item(i) !== this) {}
                    return i > -1;
                };
        }

        // Get closest match
        for ( ; elem && elem !== document; elem = elem.parentNode ) {
            if ( elem.matches( selector ) ) return elem;
        }

        return null;
    };
    
	// after changing the number of coloums 
	onChangeWidth(e) {
		this.setState({
			rows: e.target.value
		});
	}
    
	// navigate to the adding tile gridfield url
	addTile () {
		if (this.state.currentNewTile) {
			window.location.href = this.props.Addurl+'/?TileType='+this.state.currentNewTile.replace('\\', '_');
		}
	}
    
	// after moving tiles around, you want to warn the user that navigating away will lose their changes
    onSelectChanged(e) {
		// trigger form change in SS
		if(typeof(Event) === 'function') {
			var event = new Event('change', { bubbles: true });
			document.querySelector('form').dispatchEvent(event);
		}
		this.setState({
			currentNewTile: e.target.value
		});
    }
	
	// called when resizing/ moving a tile around. updates the data
	onGridChange (callback) {
		// preserve the friendly names after rearranging 
		for  (var i = 0;i<callback.length;i++) {
			for  (var n = 0;n<this.state.value.length;n++) {
				if(parseInt(callback[i].i) === parseInt(this.state.value[n].i)) {
					callback[i].n = this.state.value[n].n;
					callback[i].p = this.state.value[n].p;
					callback[i].c = this.state.value[n].c;
					callback[i].img = this.state.value[n].img;
					callback[i].disabled = this.state.value[n].disabled;
				}
			}
		}

		this.setState({ value: callback });
        
        this.registerChange();
        
        const { onChange } = this.props;
        if (onChange) {
          onChange(JSON.stringify(this.state.value));
        }
	}
	
	
	// requests to remove the tile, and on success it will remove the tile from the grid
	RemoveItem(e) {
		let requesturl = e.target.getAttribute('data-url');
		let currentid = e.target.getAttribute('data-id');
		let _self = this;

		if (confirm('Are you sure you want to delete this record?')) {
			fetch(requesturl)
				.then(_self.checkStatus)
				.then(function(response) {
					return response.text();
				}).then(function() {
					for  (var n = 0;n < _self.state.value.length;n++) {
						if(parseInt(_self.state.value[n].i) === parseInt(currentid)) {
							_self.state.value.splice(n, 1);
						}
					}
					_self.setState({ value: _self.state.value });
				}).catch(function(error) {
					alert('failed to remove tile ' + error);
				});
		}

		e.preventDefault(e);
		return false;
	}
	
	/**
	 * When the component is mounted, parse the input value (provided as JSON) and store it
	 * in local state as a structured object.
	 */
	componentDidMount() {
        this.disableDragging();
	}

	/**
	 * @returns {string}
	 */
	getClassNames() {
		const { extraClass } = this.props;

		return classnames(
            //.. 'tilefield__tileselect',
            //'form__field-holder',
            'field',  'form-group'
			//extraClass,
		);
	}


  /**
   * Enables this implementation to work within the Entwine or React context, as FormbuilderLoader
   * stores the linkedPage in data.
   *
   * @returns {string}
   */
    getLinkedPage() {
        const { linkedPage, data } = this.props;

        if (data && (!linkedPage || !Object.keys(linkedPage).length)) {
            return data.linkedPage;
        }

        return linkedPage;
    }

    /**
     * When changed, we shouldn't show the content any longer, but show a message instead
     */
    registerChange() {
        this.setState({
            isDirty: true,
        });
    }

	// disables the add button when a new tilefield is created
	RowsInput() {
		if(this.props.RowsEnabled) {
			return (
					<div className="">
					<label className="form__field-label">Number of rows</label>
					<input type="number" name={`${this.props.name}_Rows`} value={this.state.rows} className="numeric text tilefield__textinput" onChange={this.onChangeWidth} />
					</div>
					);
		}
	}
	
	// disables the add button when a new tilefield is created
	ButtonClasses() {
		let showdisabled = this.props.disabled;
		if(this.state.currentNewTile && !this.props.disabled) {
			showdisabled = false;
		}
		return 'btn btn-primary font-icon-plus tilefield__addbtn ' + (showdisabled ? 'disabled':'');
	}
	
    // build a list of avalible tile objects to create
    TileDropdown() {
        return (
            <div className="tilefield__tileselect--newline pull-xs-left">
                <div className="tilefield__tileselect field">
                    <div className="">
                        <select className="tilefield__selectholder no-change-track" onChange={this.onSelectChanged}>
                            <option value="" selected>Select new tile type</option>
                            {this.state.tiletypes.map(item => {
                                return (<option value={item.title} className="no-change-track" key={item.title}>{item.name}</option>);
                            })}
                        </select>
                    </div>
                </div>

                <a className={this.ButtonClasses()} data-icon="add" onClick={this.addTile}>Add</a>
            </div>
				);
    }
	
	// you can either have an image or some text as a preview for a tile
	PreviewThumbnail(item) {
		if(item.img) {
			return {'background-image':'url('+item.img+')'};
		}
		return {'background-color':item.c};
	}
    
	// dyanmic url to edit a current tile
	getEditURL (myid) {
		return this.props.Editurl.replace('/ID/', '/'+myid+'/');
	}
	
	// removing the current tile
	getDeleteURL (myid) {
		return this.props.Deleteurl.replace('/ID', '/'+myid);
	}
    
	// goes through and creates the tiles
	generateDOM () {
		let _self = this;
        
        return this.state.value.map(function(item){
						let disableicon = '';
						if (item.disabled == 1) {
							disableicon = <a className='font-icon-eye-with-line tilefield__eye' title='Hidden tile'></a>;
						}
                        return <div key={item.i} 
							data-grid={{x:item.x, y: item.y, w: item.w, h: item.h, maxW: item.maxW, maxH: item.maxH}}>
								<div className='tilefield__tilecontainer' style={_self.PreviewThumbnail(item)}>
									<div className='tilefield__title'>
										{item.n}
									</div>
									<div className='tilefield__actions'>
										{disableicon}
										<a href={_self.getEditURL(item.i)} className='btn action font-icon-edit' title='Edit this tile'>Edit</a>
										<a data-url={_self.getDeleteURL(item.i)} data-id={item.i} className='btn action font-icon-trash' title='Delete this tile' onClick={_self.RemoveItem}></a>
									</div>
									<div className='tilefield__clear'></div>
									<div className='tilefield__previewcontent'>
										{item.p}
									</div>
								</div>
							</div>;
                      });
	}
    
    // trying to drag in a dragging area is a pain in the ass and does not work
    componentDidUpdate() {
        this.disableDragging();
    }
    
    // we have to access the DOM and disable the gridfield drag and drop feature.
    // this does not uphold the react methods, but by default gridfield does not offer a way to do this
    disableDragging() {
        var _this = this;
        // wait for a paint to disable dragging
        window.requestAnimationFrame(function() {
            let elementcontainer = _this.getClosest(_this._container, '.element-editor__element');
            if(elementcontainer) {
                let resultn = elementcontainer.setAttribute("draggable", "false");
            }
        });
    }
    
    render() {
        const { name } = this.props;
        const { value } = this.state;

        this.disableDragging();
        return (
            <div
                className={this.getClassNames()}
                ref={c => (this._container = c)}
                tabIndex={0}
            >
            
                <div className="form__field-holder">
                    <input type="hidden" name={name} value={JSON.stringify(value)} />
                    
                    {this.TileDropdown()}
                        <div style={{ clear:'both' }}></div>
                    {this.RowsInput()}


                    <GridLayout className="layout" cols={this.state.rows} rowHeight={200} width={1000} autoSize={true} onDragStop={this.onGridChange} onResizeStop={this.onGridChange}>
                        {this.generateDOM()}
                    </GridLayout>
                </div>
            </div>
        );
    }
}





const pageShape = PropTypes.shape({
  URLSegment: PropTypes.string,
});

TileField.propTypes = {
  extraClass: PropTypes.string,
  linkedPage: pageShape,
  showLinkText: PropTypes.bool,
  onChange: PropTypes.func,
  title: PropTypes.string,
  value: PropTypes.string
};

TileField.defaultProps = {
  linkedPage: {},
  showLinkText: true,
  value: '{}'
};

export default TileField;