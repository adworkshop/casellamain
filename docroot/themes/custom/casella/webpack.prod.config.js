const path = require('path');
const merge = require('webpack-merge');
const UglifyJsWebpackPlugin = require('uglifyjs-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const webpackBaseConfig = require('./webpack.base.config.js');

module.exports = merge(webpackBaseConfig, {
  mode: 'production',
  output: {
    path: path.resolve(__dirname, './build/dist'),
    filename: '[name].bundle.js'
  },
  optimization: {
    minimizer: [
      new UglifyJsWebpackPlugin({
        extractComments: 'all',
        uglifyOptions: {
          warnings: false,
          mangle: false,
          output: {
            comments: false
          }
        }
      }),
      new OptimizeCSSAssetsPlugin()
    ]
  }
});
