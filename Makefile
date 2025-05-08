date:
	date '+%Y-%m-%d %H:%M:%S' > version
	git add version
	git commit -m 'feat: change version'
	git push

delete-tag:
	git fetch --tags
	@if [ -z "$(version)" ]; then \
		echo "Error: You must provide a version prefix (e.g., make delete-tag version=0.0)"; \
		exit 1; \
	fi
	git fetch --tags
	tags_to_delete=$$(git tag --list "$(version)-*" | tr '\n' ' '); \
	if [ -n "$$tags_to_delete" ]; then \
		git push origin --delete $$tags_to_delete; \
		echo "Deleted tags: $$tags_to_delete"; \
	else \
		echo "No tags found matching prefix: $(version)-*"; \
	fi

help:
	@echo "  make date                   - Creates a version file with the current date and time"
	@echo "  make delete-tag version=0.0 - Removes tags matching 'dev-version.*' (e.g., 'dev-0.0.*')"
