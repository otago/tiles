const path = require('path');

module.exports = {
    mode: 'development',
    devtool: 'eval-source-map',
    entry: './client/src/main.js',
    externals: {
        'lib/Injector': 'Injector'
    },
    output: {
        path: path.resolve(__dirname, 'client/dist/js'),
        filename: 'bundle.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader"
                }
            }
        ]
    }
};