#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# Define the plugin directory name (assumes your main plugin directory is the current working directory)
# The GitHub Actions runner checks out your repository into a directory like:
# /home/runner/work/advanced-study-bible-wordpress-plugin/advanced-study-bible-wordpress-plugin
# So, when this script runs, its 'pwd' should be the plugin's root.
PLUGIN_DIR=$(basename "$(pwd)")

# Define the name of the release zip file
RELEASE_ZIP="${PLUGIN_DIR}.zip" # This will be something like advanced-study-bible-wordpress-plugin.zip

# Exclude list (add any files/folders you don't want in the release zip)
# Example: .git, .github, node_modules, build directories, etc.
EXCLUDE_LIST=(
  ".git"
  ".github"
  "node_modules"
  "src" # If you have a separate build process and 'src' isn't needed in the final release
  "composer.json"
  "composer.lock"
  "Gruntfile.js" # If using Grunt
  "webpack.config.js" # If using webpack
  "package.json"
  "package-lock.json"
  "README.md" # If you have a separate WordPress readme.txt, consider keeping main README.md out
  "create-release-zip.sh" # Exclude the script itself from the zip file
)

# Build the exclude arguments for the zip command
ZIP_EXCLUDE_ARGS=""
for i in "${EXCLUDE_LIST[@]}"; do
  ZIP_EXCLUDE_ARGS+=" -x \"${i}/*\"" # Exclude directories and their contents
  ZIP_EXCLUDE_ARGS+=" -x \"${i}\""   # Exclude files
done

# Create the single-nested zip file
# Important: We want to zip the contents of the *plugin directory*
# directly into a zip file named after the plugin, and place that
# zip file in the *parent directory* (repository root) where the action can find it.

# Move up one directory to be at the repository root
cd ..

# Create the zip file:
#   -r : Recurse into directories
#   "$RELEASE_ZIP" : The name of the output zip file (e.g., advanced-study-bible-wordpress-plugin.zip)
#   "$PLUGIN_DIR" : The directory to be zipped (e.g., advanced-study-bible-wordpress-plugin)
#   $ZIP_EXCLUDE_ARGS : Apply the exclusion rules
zip -r "$RELEASE_ZIP" "$PLUGIN_DIR" $ZIP_EXCLUDE_ARGS

# The zip file is now in the repository root (e.g., /home/runner/work/your-repo/your-repo/advanced-study-bible-wordpress-plugin.zip)

echo "Created release zip: $RELEASE_ZIP"
ls -lh "$RELEASE_ZIP" # List the created file to confirm size
