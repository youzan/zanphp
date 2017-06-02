usage = "\
Usage:                make <option> \n\n\
authors               更新贡献者"

default:
	@echo $(usage)

# Update Authors
authors:
	git log --format='%aN <%aE>' | sort -u > AUTHORS