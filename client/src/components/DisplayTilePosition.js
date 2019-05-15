const DisplayTilePosition = ({ tile }) => {
  return (
    <div>
      <span>{`${tile.x}, ${tile.y}`}</span>
    </div>
  );
};

export default DisplayTilePosition;
