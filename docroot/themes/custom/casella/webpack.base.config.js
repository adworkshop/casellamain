const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');

const config = {
  entry: {
    'casella': './src/casella.js'
  },

  output: {
    path: path.resolve(__dirname, './build/dev'),
    filename: '[name].bundle.js'
  },

  module: {
    rules: [
      {
        text: /\.js$/,
        exclude: /node_modules/,
        //use: ['babel-loader']
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      }
    ]
  },

  plugins: [
    new HtmlWebpackPlugin({
      template: './build/dev/index.html',
      filename: 'index.html',
      inject: true,
      hash: true
    })
  ],

  watch: true,
  devtool: 'source-map'
};

module.exports = config;
