PKG_NAME = php-scrypt
PKG_FULL_NAME = php-scrypt
PKG_VERS = v1.2
PKG_EXT = tar.gz
PKG_DIST_NAME = $(PKG_VERS).$(PKG_EXT)
PKG_DIST_SITE = https://github.com/DomBlack/$(PKG_FULL_NAME)/archive
PKG_DIR = $(PKG_FULL_NAME)-$(PKG_VERS)

DEPENDS =

HOMEPAGE = https://github.com/DomBlack
COMMENT  = A PHP wrapper fo the scrypt hashing algorithm
LICENSE  = BSD 2-Clause license.

CONFIGURE_TARGET = nop
COMPILE_TARGET = nop
INSTALL_TARGET = myInstall

include ../../mk/spksrc.cross-cc.mk

myInstall:
	echo "cd $(WORK_DIR)/php-scrypt-1.2" > $(WORK_DIR)/php-scrypt-1.2/i.sh
	echo "phpize" >> $(WORK_DIR)/php-scrypt-1.2/i.sh
	echo "./configure --enable-scrypt" >> $(WORK_DIR)/php-scrypt-1.2/i.sh
	echo "make" >> $(WORK_DIR)/php-scrypt-1.2/i.sh
	echo "make install" >> $(WORK_DIR)/php-scrypt-1.2/i.sh
	chmod +x  $(WORK_DIR)/php-scrypt-1.2/i.sh
	$(WORK_DIR)/php-scrypt-1.2/i.sh
