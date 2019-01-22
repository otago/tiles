import Injector from 'lib/Injector';
import TileField from 'components/TileField/TileField';

export default () => {
  Injector.component.register(
    'TileField',
    TileField
  );
};
