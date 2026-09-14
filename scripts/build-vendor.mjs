import { build } from 'esbuild';
import { copyFile, stat, readFile, writeFile } from 'node:fs/promises';
import { gzipSync } from 'node:zlib';

await build({ entryPoints: ['node_modules/three/build/three.module.js'], outfile: 'vendor/three.module.js', minify: true, format: 'esm', legalComments: 'inline', target: 'es2020' });
await copyFile('node_modules/lenis/dist/lenis.min.js', 'vendor/lenis.min.js');
await copyFile('node_modules/lenis/LICENSE', 'vendor/LENIS-LICENSE.txt');
for (const file of ['vendor/three.module.js', 'vendor/lenis.min.js']) {
  await writeFile(`${file}.gz`, gzipSync(await readFile(file), { level: 9 }));
}
const before = (await stat('node_modules/three/build/three.module.js')).size;
const after = (await stat('vendor/three.module.js')).size;
console.log(`Three.js: ${before} → ${after} bytes. Lenis vendored locally.`);
