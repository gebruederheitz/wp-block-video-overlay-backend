# Run basic linting with prettier & phpstan
lint:
	@cd util/ \
		&& npm i \
		&& npm run lint
	@composer lint

# Make prettier process and fix all files in src/
prettier:
	@cd util/ \
		&& npm i \
		&& npx prettier -w --config .prettierrc ../Gebruederheitz

# Create a tagged release to publish a new version of the package
release:  lint
	@cd util/ \
		&& npm i \
		&& npm run release

