#!/usr/local/bin/python2.7

"""
    Copyright (c) 2015 Deciso B.V. - Ad Schellevis
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

    1. Redistributions of source code must retain the above copyright notice,
     this list of conditions and the following disclaimer.

    2. Redistributions in binary form must reproduce the above copyright
     notice, this list of conditions and the following disclaimer in the
     documentation and/or other materials provided with the distribution.

    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.

    --------------------------------------------------------------------------------------
    update captive portal statistics
"""
import sys
import time
import syslog
import traceback
import subprocess
import socket
from lib import Config
from lib.db import DB
from lib.arp import ARP
from lib.ipfw import IPFW
from lib.daemonize import Daemonize


class CPBackgroundJob(object):
    """ background job helper class
    """
    def __init__(self):
        # open syslog and notice startup
        syslog.openlog('captiveportal', logoption=syslog.LOG_DAEMON, facility=syslog.LOG_LOCAL4)
        syslog.syslog(syslog.LOG_NOTICE, 'starting captiveportal background job')
        # handles to ipfw, arp the config and the internal administration
        self.ipfw = IPFW()
        self.db = DB()



def main():
    """ Background process loop, runs as backend daemon for all zones. only one should be active at all times.
        The main job of this procedure is to sync the administration with the actual situation in the ipfw firewall.
    """
    last_cleanup_timestamp = 0
    bgjob = CPBackgroundJob()

    while True:
        try:
            # open database
            bgjob.db.open()

            # get wanwhiteset list
            wanwhitesetlist = bgjob.db.list_wanwhiteset()

            t = int(time.time())
            # process wanwhiteset list
            for wanwhiteset in wanwhitesetlist:
                print wanwhiteset
                if wanwhiteset['delete_time'] == 0 and wanwhiteset['expire_time'] < t:
                    bgjob.ipfw.delete_from_table(str(wanwhiteset['fwtable']), wanwhiteset['ip'])
                    bgjob.db.del_wanwhiteset(0, wanwhiteset['fwtable'], wanwhiteset['ip'], True)
                elif wanwhiteset['delete_time'] != 0 and wanwhiteset['delete_time'] < t - 20:
                    bgjob.db.del_wanwhiteset(0, wanwhiteset['fwtable'], wanwhiteset['ip'], False)
            # sleep
            time.sleep(10)
        except KeyboardInterrupt:
            break
        except SystemExit:
            break
        except:
            syslog.syslog(syslog.LOG_ERR, traceback.format_exc())
            print(traceback.format_exc())
            break

# startup
if len(sys.argv) > 1 and sys.argv[1].strip().lower() == 'run':
    main()
else:
    daemon = Daemonize(app=__file__.split('/')[-1].split('.py')[0], pid='/var/run/captiveportal_job.db.pid', action=main)
    daemon.start()
