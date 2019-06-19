const path = require('path');
const MiniCSSExtractPlugin = require('mini-css-extract-plugin');

const config = {
  entry: {
    'contactForm': './src/contactForm.js'
  },

  output: {
    path: path.resolve(__dirname, './build/dev'),
    filename: '[name].bundle.js'
  },

  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        //use: ['babel-loader']
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      {
        test: /\.css$|.scss$/,
        use: [
          MiniCSSExtractPlugin.loader,
          'css-loader',
          'sass-loader',
          'postcss-loader'
        ]
      }
    ]
  },

  plugins: [
    new MiniCSSExtractPlugin({
      filename: '[name].bundle.css'
    })
  ],

  watch: true,
  devtool: 'source-map'
};

module.exports = config;
