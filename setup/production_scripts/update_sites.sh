## noisy script for updating alpha and production on git commits.

[ "$USER" == "bwrox" ] || { echo "this script should only be run as bwrox user"; exit 255; }

#SITES="alpha.bewelcome.org"
SITES="alpha.bewelcome.org www.bewelcome.org"
TMPFILE=/home/bwrox/tmp/updates.$$

for SITE in ${SITES}
do

## TODO: figure out how to do this under git
if false 
then
cd /home/bwrox/${SITE}
if ! svn stat --show-updates > ${TMPFILE} 2>&1
then
  date >> /home/bwrox/${SITE}/htdocs/update_status.txt
  echo "Update not successful.  Manual sysadm intervension needed.  Output of svn stat --show-updates follows:" >> /home/bwrox/${SITE}/htdocs/update_status.txt
  cat ${TMPFILE} >> /home/bwrox/${SITE}/htdocs/update_status.txt
fi

## then check for conflicts
if grep -q '^[\?MA] *\*  ' ${TMPFILE}
then
  cat ${TMPFILE} | mail -s "${SITE} NOT updated after commit - conflicts" bw-admin-discussion@bewelcome.org bw-dev-svn@bewelcome.org
  echo -e "\n\n" >> /home/bwrox/${SITE}/htdocs/update_status.txt
  date >> /home/bwrox/${SITE}/htdocs/update_status.txt
  echo "Update not successful.  Manual sysadm intervension needed.  Output of svn stat --show-updates follows:" >> /home/bwrox/${SITE}/htdocs/update_status.txt
  cat ${TMPFILE} >> /home/bwrox/${SITE}/htdocs/update_status.txt
  continue
fi
fi

state="Successful"
echo -n "Update starting on " > ${TMPFILE}
date >> ${TMPFILE}
echo -e "\n\nOutput of git pull on ${SITE}" >> ${TMPFILE}
git pull >> ${TMPFILE} 2>&1 || state="UNSUCCESSFUL"
grep -q 'Already up-to-date' ${TMPFILE} && continue

echo -e "\n\nUpdate finished " >> ${TMPFILE}
date >> ${TMPFILE}
echo -e "\n\nOutput of git status on ${SITE}" >> ${TMPFILE}
git status >> ${TMPFILE}
echo -e "\n\nOutput of git show on ${SITE}" >> ${TMPFILE}
git show >> ${TMPFILE}
echo -e "\n\nOutput of git diff on ${SITE}" >> ${TMPFILE}
git diff >> ${TMPFILE}
branch=`git branch | grep -E '^\*' | cut -f2 -d' '`
echo -e "\n\nOutput of git diff origin/$branch $branch on ${SITE}" >> ${TMPFILE}
git diff origin/$branch $branch >> ${TMPFILE}
echo -e "\n\nState of update: $state" >> ${TMPFILE}

mail -s "${state} update of ${SITE}" bw-dev-svn@bewelcome.org < ${TMPFILE}

cp ${TMPFILE} /home/bwrox/${SITE}/htdocs/update_status.txt
git show --pretty=oneline | head -n1 > /home/bwrox/${SITE}/htdocs/revision.txt

done
