#include "cg_local.h"

#define DEFAULTHUD		"hud/default.dat"

// name elementid params dimensions
helements_t hude[MAX_ELEMENTS] = {
		{ "",				H_NULL,				0,	0 },
		{ "healthbar",		H_HEALTHBAR,		3,	1 },
		{ "staminabar",		H_STAMINABAR,		3,	1 },
		{ "chargebar",		H_CHARGEBAR,		3,	1 },
		{ "compass",		H_COMPASS,			4,	1 },
		{ "healthtext",		H_HP,				3,	1 },
		{ "xptext",			H_XP,				3,	1 },
		{ "upperright",		H_UPPERRIGHT,		2,	1 },
		{ "skillpic",		H_SKILLPICS,		3,	2 },
		{ "skilltext",		H_SKILLTEXTS,		3,	2 },
		{ "skillbox",		H_SKILLBOXES,		3,	2 },
		{ "overheat",		H_OVERHEAT,			4,	1 },
		{ "weaponcard",		H_WEAPONCARD,		3,	1 },
		{ "ammocount",		H_AMMO,				3,	1 },
		{ "fireteam",		H_FIRETEAM,			3,	1 },
		{ "cptext",			H_CP,				2,	1 },
		{ "bptext",			H_BP,				2,	1 },
		{ "stance",			H_STANCE,			4,	1 },
		{ "flag",			H_FLAG,				3,	1 },
		{ "treasure",		H_TREASURE,			3,	1 },
		{ "pmitems",		H_PM,				3,	1 },
		{ "bigpmitems",		H_PMBIG,			3,	1 },
		{ "head",			H_HEAD,				4,	1 },
		{ "cash",			H_CASH,				3,	1 },
};

        
void CG_LoadHud2(char *filename) {
	int			handle;
	int			x, y, z;
	pc_token_t	token;
	pc_token_t	saved;
	float		version;

	memset(&cg.hud2, 0, sizeof(cg.hud2));

	handle = trap_PC_LoadSource( filename );
	if (!handle) {
		// inform the user that this hud does not exist
		CG_Printf("CG_LoadHud: file '%s' does not exist, attempting to load default hud.\n", filename);

		// see if we can load from the cvar
		filename = va("hud/%s.dat", cb_defaulthud.string);
		handle = trap_PC_LoadSource( filename );
		if (!handle) {
			// return a warning
			CG_Printf("CG_LoadHud: Failed to load default hud.\n");

			// now try the hardcoded hud, this *SHOULD* exist for all clients...
			handle = trap_PC_LoadSource( DEFAULTHUD );
			if (!handle) {
				CG_Printf("CG_LoadHud: (^1CRITICAL ERROR^7) ^3NO HUD FOUND!^7\n");
				return;
			}
		}
	}

	// now let's read the elements
	while (1) {
		if ( !trap_PC_ReadToken( handle, &token ) ) {
			break;
		}

		// find the version (this is incompatible with version 1 files)
		if (!Q_stricmp( token.string, "version") ) {
			saved = token;
			// read the next token
			if ( !trap_PC_ReadToken( handle, &token ) ) {
				CG_Printf("Error reading after token '%s'\n", saved.string);
				break;
			}
			version = token.floatvalue;

			if (version < 2) {
				CG_Printf("Hud contains invalid or empty version.\n");
				return;
			}
		}

		if ( !Q_stricmp( token.string, "elements") ) {
			// first we *MUST* have a version (version 1 breaks our parsing)
			if (version == 0) {
				CG_Printf("Hud contains invalid or empty version.\n");
				return;
			}

			saved = token;
			// read the next token
			if ( !trap_PC_ReadToken( handle, &token ) ) {
				CG_Printf("Error reading after token '%s'\n", saved.string);
				return;
			}

			// bracket
			if ( !Q_stricmp(token.string, "{") ) {
				while ( 1 ) {
					// read a token
					saved = token;
					if ( !trap_PC_ReadToken( handle, &token ) ) {
						CG_Printf("Error reading after token '%s'\n", saved.string);
						return;
					}

					// end bracket
					if ( !Q_stricmp( token.string, "}") ) {
						break;
					}

					// see if this element exists
					for (x = 0; x < MAX_ELEMENTS; x++) {
						// force compass to load anyways
						if ( !Q_stricmp( token.string, hude[x].name ) ) {
						//if ( !Q_stricmp( token.string, hude[x].name ) ) {
							// first check to see if we have only 1 param!
							if ( hude[x].params == 1 ) {
								cg.hud2[hude[x].element][0][0] = token.intvalue;
							} else if ( hude[x].dimensions == 1 ) {
								for (y = 0; y < hude[x].params; y++) {

									saved = token;
									if ( !trap_PC_ReadToken( handle, &token ) ) {
										CG_Printf("Error reading after token '%s'\n", saved.string);
										return;
									}
									cg.hud2[hude[x].element][0][y] = token.intvalue;
								}
							} else {
								for ( y = 0; y <= hude[x].dimensions; y++ ) {
									for (z = 0; z < hude[x].params; z++ ) {

										saved = token;
										if ( !trap_PC_ReadToken( handle, &token ) ) {
											CG_Printf("Error reading after token '%s'\n", saved.string);
											return;
										}
										cg.hud2[hude[x].element][y][z] = token.intvalue;
									}
								}
							}
						}
					}
				}
			}
		}
	}

	trap_PC_FreeSource( handle );
}