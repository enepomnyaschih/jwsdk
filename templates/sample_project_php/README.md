This is a sample of PHP integration with jWidget SDK.

Configure Apache:

- DocumentRoot must refer to `public`
- mod_dir.so and mod_rewrite.so are on
- DirectoryIndex index.php
- Directory configuration: AllowOverride All

Build project from project root directory:

jwsdk <mode> jwsdk-config
<mode> is "debug" or "release"

Pages:

- http://localhost
- http://localhost/easy.php
- http://localhost/difficult.php
