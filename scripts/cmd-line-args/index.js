/**
 * @description Get the arguments from command-line
 * @usage "getCmdArgs.mode" if "--mode" is extracted from command-line
 * @param The command line is automatically processed
 * @return array<int, array<string, string>> An array with extracted arguments
 */
 const getCmdArgs = (argList => {
	let arg = {}, mode, opt, thisOpt, curOpt;
	for (mode = 0; mode < argList.length; mode++) {
		thisOpt = argList[mode].trim();
		opt = thisOpt.replace(/^\-+/, '');
		if (opt === thisOpt) {
			// argument value
			if (curOpt) arg[curOpt] = opt;
			curOpt = null;
		} else {
			// argument name
			curOpt = opt;
			arg[curOpt] = true;
		}
	}
	return arg;
})(process.argv);
 
module.exports = getCmdArgs;
