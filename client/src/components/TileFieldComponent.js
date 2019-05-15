// import React from 'react';
import React from '../../../node_modules/react/index';
import GridLayout from 'react-grid-layout';
import 'whatwg-fetch';
import PropTypes from 'prop-types';
import DisplayTilePosition from './DisplayTilePosition';

class TileFieldComponent extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      currentNewTile: '',
      items: this.props.list,
      rows: this.props.rows
    };

    this.onSelectChanged = this.onSelectChanged.bind(this);
    this.addTile = this.addTile.bind(this);
    this.onGridChange = this.onGridChange.bind(this);
    this.RemoveItem = this.RemoveItem.bind(this);
    this.onChangeWidth = this.onChangeWidth.bind(this);
  }

  // navigate to the adding tile gridfield url
  addTile() {
    console.log('addTile()');
    if (this.state.currentNewTile) {
      window.location.href =
        this.props.addurl +
        '/?TileType=' +
        this.state.currentNewTile.replace('\\', '_');
    }
  }

  // after moving tiles around, you want to warn the user that navigating away will lose their changes
  onSelectChanged(e) {
    console.log('onSelectChanged(e)');
    // trigger form change in SS
    if (typeof Event === 'function') {
      var event = new Event('change', { bubbles: true });
      document.querySelector('form').dispatchEvent(event);
    }
    this.setState({
      currentNewTile: e.target.value
    });
  }

  // dyanmic url to edit a current tile
  getEditURL(myid) {
    console.log('getEditURL(myid)');
    return this.props.editurl.replace('/ID/', '/' + myid + '/');
  }

  // removing the current tile
  getDeleteURL(myid) {
    console.log('getDeleteURL(myid)');
    return this.props.deleteurl.replace('/ID', '/' + myid);
  }

  // you can either have an image or some text as a preview for a tile
  PreviewThumbnail(item) {
    console.log('PreviewThumbnail(item)');
    if (item.img) {
      return { 'background-image': 'url(' + item.img + ')' };
    }
    return { 'background-color': item.c };
  }

  // we use ajax to remove tiles. this function checks the response to make sure it's ok
  checkStatus(response) {
    console.log('checkStatus(response)');
    if (response.status >= 200 && response.status < 300) {
      return response;
    } else {
      var error = new Error(response.statusText);
      error.response = response;
      throw error;
    }
  }

  // requests to remove the tile, and on success it will remove the tile from the grid
  RemoveItem(e) {
    console.log('RemoveItem(e)');
    let requesturl = e.target.getAttribute('data-url');
    let currentid = e.target.getAttribute('data-id');
    let _self = this;

    if (confirm('Are you sure you want to delete this record?')) {
      fetch(requesturl)
        .then(_self.checkStatus)
        .then(function(response) {
          return response.text();
        })
        .then(function() {
          for (var n = 0; n < _self.state.items.length; n++) {
            if (parseInt(_self.state.items[n].i) === parseInt(currentid)) {
              _self.state.items.splice(n, 1);
            }
          }
          _self.setState({ items: _self.state.items });
        })
        .catch(function(error) {
          alert('failed to remove tile ' + error);
        });
    }

    e.preventDefault(e);
    return false;
  }

  // goes through and creates the tiles
  generateDOM() {
    console.group('generateDOM()');
    console.log('this.state.items => ', this.state.items);
    console.groupEnd();
    let _self = this;
    return this.state.items.map(function(item) {
      let disableicon = '';
      if (item.disabled == 1) {
        disableicon = (
          <a
            className="font-icon-eye-with-line tilefield__eye"
            title="Hidden tile"
          />
        );
      }
      return (
        <div
          key={item.i}
          data-grid={{
            x: item.x,
            y: item.y,
            w: item.w,
            h: item.h,
            maxW: item.maxW,
            maxH: item.maxH
          }}>
          <div
            className="tilefield__tilecontainer"
            style={_self.PreviewThumbnail(item)}>
            <div className="tilefield__title">{item.n}</div>
            <div>
              <DisplayTilePosition tile={item} />
            </div>
            <div className="tilefield__actions">
              {disableicon}
              <a
                href={_self.getEditURL(item.i)}
                className="btn action font-icon-edit"
                title="Edit this tile">
                Edit
              </a>
              <a
                data-url={_self.getDeleteURL(item.i)}
                data-id={item.i}
                className="btn action font-icon-trash"
                title="Delete this tile"
                onClick={_self.RemoveItem}
              />
            </div>
            <div className="tilefield__clear" />
            <div className="tilefield__previewcontent">{item.p}</div>
          </div>
          <input
            type="hidden"
            name={`Tiles[GridLayout][${item.i}][x]`}
            value={item.x}
            className="hidden form-group--no-label"
          />
          <input
            type="hidden"
            name={`Tiles[GridLayout][${item.i}][y]`}
            value={item.y}
            className="hidden form-group--no-label"
          />
          <input
            type="hidden"
            name={`Tiles[GridLayout][${item.i}][w]`}
            value={item.w}
            className="hidden form-group--no-label"
          />
          <input
            type="hidden"
            name={`Tiles[GridLayout][${item.i}][h]`}
            value={item.h}
            className="hidden form-group--no-label"
          />
        </div>
      );
    });
  }

  // called when resizing/ moving a tile around. updates the data
  onGridChange(callback) {
    console.log('onGridChange(callback)');
    // preserve the friendly names after rearranging
    for (var i = 0; i < callback.length; i++) {
      for (var n = 0; n < this.state.items.length; n++) {
        if (parseInt(callback[i].i) === parseInt(this.state.items[n].i)) {
          callback[i].n = this.state.items[n].n;
          callback[i].p = this.state.items[n].p;
          callback[i].c = this.state.items[n].c;
          callback[i].img = this.state.items[n].img;
          callback[i].disabled = this.state.items[n].disabled;
        }
      }
    }

    this.setState({ items: callback });
  }

  // disables the add button when a new tilefield is created
  ButtonClasses() {
    console.log('ButtonClasses()');
    let showdisabled = this.props.disabled;
    if (this.state.currentNewTile && !this.props.disabled) {
      showdisabled = false;
    }
    return (
      'btn btn-primary font-icon-plus tilefield__addbtn ' +
      (showdisabled ? 'disabled' : '')
    );
  }

  // after changing the number of coloums
  onChangeWidth(e) {
    console.log('onChangeWidth(e)');
    this.setState({
      rows: e.target.value
    });
  }

  // disables the add button when a new tilefield is created
  RowsInput() {
    console.log('RowsInput()');
    if (this.props.rowsenabled) {
      return (
        <div>
          <label className="form__field-label">Number of rows</label>
          <input
            type="number"
            name={`${this.props.name}[Rows]`}
            value={this.state.rows}
            className="numeric text tilefield__textinput"
            onChange={this.onChangeWidth}
          />
        </div>
      );
    }
  }

  // makes all the tiles :)
  render() {
    console.log('render()');
    return (
      <div className="tilefield__container">
        {this.RowsInput()}
        <div className="pull-xs-left ss-gridfield-add-new-multi-class">
          <div className="form-group field">
            <div className="form__field-holder">
              <select
                className="tilefield__selectholder no-change-track"
                onChange={this.onSelectChanged}>
                <option value="" selected>
                  Select new tile type
                </option>
                {this.props.tiletypes.map(item => {
                  return (
                    <option
                      value={item.title}
                      className="no-change-track"
                      key={item.title}>
                      {item.name}
                    </option>
                  );
                })}
              </select>
            </div>
          </div>

          <a
            className={this.ButtonClasses()}
            data-icon="add"
            onClick={this.addTile}>
            Add
          </a>
        </div>
        <div className="tilefield__clear" />
        <GridLayout
          className="layout"
          cols={this.state.rows}
          rowHeight={200}
          width={1000}
          autoSize={true}
          onDragStop={this.onGridChange}
          onResizeStop={this.onGridChange}>
          {this.generateDOM()}
        </GridLayout>
      </div>
    );
  }
}

TileFieldComponent.propTypes = {
  list: PropTypes.array,
  deleteurl: PropTypes.string,
  tiletypes: PropTypes.array,
  addurl: PropTypes.string,
  editurl: PropTypes.string,
  disabled: PropTypes.bool,
  name: PropTypes.string,
  rows: PropTypes.number,
  rowsenabled: PropTypes.bool
};

export default TileFieldComponent;
