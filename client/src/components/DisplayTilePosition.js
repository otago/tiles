const DisplayTilePosition = ({ tile }) => {
  return (
    <div className="tilefield__position">
      <span>{`${tile.x}, ${tile.y}`}</span>
    </div>
  );
};

export default DisplayTilePosition;
