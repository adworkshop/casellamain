const path = require('path');
const MiniCSSExtractPlugin = require('mini-css-extract-plugin');

const config = {
  entry: {
    'contactForm': './src/contactForm.js'
    // 'casella': ['./src/casella.ts', './src/casella.css']
  },

  output: {
    path: path.resolve(__dirname, './build/dev'),
    filename: '[name].bundle.js'
  },

  module: {
    rules: [
      {
        test: [/.js$|.ts$/],
        exclude: /(node_modules)/,
        //use: ['babel-loader']
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/typescript',
              '@babel/preset-env'
            ]
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
      },
      {
        test: /\.(jpg|jpeg|gif|png)$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[hash.8].[ext]',
              outputPath: 'assets/'
            }
          }
        ]
      }
    ]
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src')
    },
    modules: [
      'node_modules',
      path.resolve(__dirname, './src')
    ],
    extensions: ['.js', '.ts']
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
