#define _GNU_SOURCE
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

int main(int argc, char *argv[]) {
	setuid(0);
	clearenv();

	int allocated = 0;
	char *commandLine;

	if (argc > 1) {
		if ((strcmp("password", argv[1]) == 0) && argc == 3) {
			allocated = asprintf(&commandLine, "/usr/local/vpn/updateAdminPassword.sh %s", argv[2]);
			printf("Executing: %s\n", commandLine);
			if (allocated > 0) {
				system(commandLine);
				free(commandLine);
			}
		} else if ((strcmp("network", argv[1]) == 0) && argc == 2) {
			system("/usr/local/vpn/configNetwork.sh");
		} else if ((strcmp("vpnConfig", argv[1]) == 0) && argc == 2) {
			system("/usr/local/vpn/getVPNConfig.sh");
		} else if ((strcmp("server", argv[1]) == 0) && argc == 2) {
			system("/usr/local/vpn/configServer.sh");
		} else if ((strcmp("client", argv[1]) == 0) && argc == 3) {
			allocated = asprintf(&commandLine, "/usr/local/vpn/configClient.sh %s", argv[2]);
			printf("Executing: %s\n", commandLine);
			if (allocated > 0) {
				system(commandLine);
				free(commandLine);
			}
		} else if ((strcmp("clientkeys", argv[1]) == 0) && argc == 2) {
			system("/usr/local/vpn/generateVPNClientKeys.sh");
		} else if ((strcmp("clientremove", argv[1]) == 0) && argc == 2) {
			system("/usr/local/vpn/removeClient.sh");
		} else if ((strcmp("factory", argv[1]) == 0) && argc == 2) {
			system("/usr/local/vpn/restoreFactoryDefaults.sh");
		} else {
			printf("Incorrect/Invalid Input Provided!\n");
			for (allocated = 0; allocated < argc; allocated++) {
				printf("argv[%d]: [%s]\n", allocated, argv[allocated]);
			}
			printf("argc: [%d]\n", argc);
		}
	} else {
		printf("No Input Provided\nRunning as User:\n");
		system("/usr/bin/whoami");
		return 1;
	}

	return 0;
}
