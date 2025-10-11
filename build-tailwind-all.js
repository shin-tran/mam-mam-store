import { readdirSync } from "fs";
import { spawn } from "child_process";
import { join, extname } from "path";

const srcDir = "./resources/css";
const outDir = "./public/css";

const files = readdirSync(srcDir).filter((f) => extname(f) === ".css");

files.forEach((file) => {
  const input = join(srcDir, file);
  const output = join(outDir, file);
  const child = spawn(
    "bunx",
    ["@tailwindcss/cli", "-i", input, "-o", output, "--watch"],
    { stdio: "inherit" }
  );

  child.on("error", (err) => {
    console.error(`Error building ${file}:`, err);
  });
});
