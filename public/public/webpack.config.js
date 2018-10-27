const webpack = require('webpack');
const path = require('path');

module.exports = {
  mode: "production",
  performance: {
    hints: 'warning',
    maxAssetSize: 250000, //单文件超过250k，命令行告警
    maxEntrypointSize: 250000 //首次加载文件总和超过250k，命令行告警
  },
  cache: true,
  entry: {
    'profile': __dirname + '/js/profile/main.js',
    'login': __dirname + '/js/login/main.js',
    'builds': path.resolve('./js/builds/main.js'),
  },
  output: {
    path: __dirname + '/assets/js',
    filename: "[name].js",
    pathinfo: true
  },
  devtool: 'none',
  devServer: {
    contentBase: "./",
    historyApiFallback: true,
    inline: true,
    hot: true,
  },
  plugins: [],
  module: {
    rules: [
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader'],
        include: path.join(__dirname, './src'),
        exclude: /node_modules/
      }
    ],
  },
  optimization: {
    minimize: true, //是否进行代码压缩
    noEmitOnErrors: true //取代 new webpack.NoEmitOnErrorsPlugin()，编译错误时不打印输出资源。
  }
};
