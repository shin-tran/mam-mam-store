import { glob } from "glob";
import path from "path";
import { fileURLToPath } from "url";
import WebpackObfuscator from "webpack-obfuscator";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default {
  entry: glob.sync("./resources/ts/**/*.ts").reduce((acc, filePath) => {
    const entryName = path
      .relative("./resources/ts", filePath)
      .replace(/\.ts$/, "");
    acc[entryName] = `./${filePath}`;
    return acc;
  }, {}),

  output: {
    filename: "[name].js", // Tự động thay name bằng các entry
    path: path.resolve(__dirname, "public/js"),
    clean: true, // Xóa thư mục output trước mỗi lần build
  },

  module: {
    rules: [
      {
        test: /\.ts$/,
        use: "ts-loader",
        exclude: /node_modules/,
      },
    ],
  },

  plugins: [new WebpackObfuscator({}, [])],

  resolve: {
    extensions: [".ts", ".js"],
  },

  mode: "production", // development, production or none
};
