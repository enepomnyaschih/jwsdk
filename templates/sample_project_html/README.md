This is a sample of project which will be compiled to plain html files.

Configure Apache:

- DocumentRoot must refer to `public`
- mod_dir.so and mod_rewrite.so are on
- DirectoryIndex index.html
- Directory configuration: AllowOverride All

Build project from project root directory:

jwsdk <mode> jwsdk-config
<mode> is "debug" or "release"

Pages:

- http://localhost
- http://localhost/easy
- http://localhost/difficult
