#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# Define the plugin directory name (assumes your main plugin directory is the current working directory)
# When this script runs in GitHub Actions, PWD will be the plugin's root directory:
# /home/runner/work/your-repo-name/your-repo-name/
PLUGIN_DIR=$(basename "$(pwd)")

# Define the name of the release zip file. This will be created in the *parent directory* (repository root).
RELEASE_ZIP="${PLUGIN_DIR}.zip" # This will be e.g., advanced-study-bible-wordpress-plugin.zip

# Exclude list (customize this for your project)
EXCLUDE_LIST=(
  ".git"
  ".github"
  "node_modules"
  "src" # Example, exclude if source isn't needed in release
  "composer.json"
  "composer.lock"
  "Gruntfile.js" # Example
  "webpack.config.js" # Example
  "package.json"
  "package-lock.json"
  "README.md" # Example, if you have a separate WordPress readme.txt
  "create-release-zip.sh" # Exclude the script itself
)

# Build the exclude arguments for the zip command
ZIP_EXCLUDE_ARGS=""
for i in "${EXCLUDE_LIST[@]}"; do
  ZIP_EXCLUDE_ARGS+=" -x \"${i}/*\"" # Exclude directories and their contents
  ZIP_EXCLUDE_ARGS+=" -x \"${i}\""   # Exclude files
done

# Move up one directory to the repository root before creating the zip.
# This places the generated zip file directly in the repository's root,
# where the GitHub action can easily find it using a simple pattern.
cd ..

# Create the single-nested zip file
zip -r "$RELEASE_ZIP" "$PLUGIN_DIR" $ZIP_EXCLUDE_ARGS

echo "Created release zip: $RELEASE_ZIP"
ls -lh "$RELEASE_ZIP" # Optional: List the created file to confirm size and location
