const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
module.exports = {
  mode: 'development',
  entry: {
    'js/app': './src/js/app.js',
    'js/inicio': './src/js/inicio.js',
    'js/ventas': './src/js/ventas.js',
    'js/compras': './src/js/compras.js',
    'js/clientes': './src/js/clientes.js',
    'js/proveedores': './src/js/proveedores.js',
    'js/productos': './src/js/productos.js',
    'js/turnos': './src/js/turnos.js',
    'js/inventario': './src/js/inventario.js',
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'public/build')
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: 'styles.css'
    })
  ],
  module: {
    rules: [
      {
        test: /\.(c|sc|sa)ss$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader
          },
          'css-loader',
          'sass-loader'
        ]
      },
      {
        test: /\.(png|svg|jpe?g|gif)$/,
        type: 'asset/resource',
      },
    ]
  }
};