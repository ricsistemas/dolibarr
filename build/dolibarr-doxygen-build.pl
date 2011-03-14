#!/usr/bin/perl
#--------------------------------------------------------------------
# Lance la generation de la doc dev doxygen
#
# \version	$Id: dolibarr-doxygen-build.pl,v 1.2 2010/07/21 11:21:37 eldy Exp $
#--------------------------------------------------------------------

# Detecte repertoire du script
($DIR=$0) =~ s/([^\/\\]+)$//;
$DIR||='.';
$DIR =~ s/([^\/\\])[\\\/]+$/$1/;

$OPTIONS="";
#$OPTIONS="-d Preprocessor";

$CONFFILE="dolibarr-doxygen.doxyfile";

use Cwd;
my $dir = getcwd;
    
print "Current dir is: $dir\n";
print "Running dir for doxygen must be: $DIR/doxygen\n";

if (! -s $CONFFILE)
{
    print "Error: current directory for building Dolibarr doxygen documentation is not correct.\n";
    print "\n";
	print "Change your current directory then, to launch the script, run:\n";
	print '> perl ..\dolibarr-doxygen-build.pl  (on Windows)'."\n";
	print '> perl ../dolibarr-doxygen-build.pl  (on Linux or BSD)'."\n";
    sleep 4;
    exit 1;   
}

print "Running doxygen, please wait...\n";
$result=`doxygen $OPTIONS $CONFFILE 2>&1`;

print $result;

0;
