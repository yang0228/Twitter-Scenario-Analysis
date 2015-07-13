import time
import boto
import boto.ec2.blockdevicemapping as BEM
from boto.ec2.regioninfo import RegionInfo


def deleteContent(pfile):
    with open(pfile, "w"):
        pass

def get_or_make_group(conn, name):
    groups = conn.get_all_security_groups()
    group = [g for g in groups if g.name == name]
    if len(group) > 0:
        return group[0]
    else:
        print "Creating security group " + name
        return conn.create_security_group(name, "EC2 group")

if __name__ == "__main__":

    import sys
    deleteContent("just_ip")
    deleteContent("role_ip")
    counter = 0 
    for i in range(0, 8):

        r=RegionInfo(name='melbourne',endpoint='nova.rc.nectar.org.au')
        ec2_conn = boto.connect_ec2(aws_access_key_id='63dd8a98739a465880ecb1aa9a6b57ee',aws_secret_access_key='a6f60c56a8db42e0b4acf2da5f744d25',is_secure=True,region=r, port=8773,path='/services/Cloud',validate_certs=False)

        images = ec2_conn.get_all_images()
        for img in images:
            if "NeCTAR Ubuntu 13.10" in img.name:
                image_id = img.id
                break
        print img.id
        #print 'id:',img.id,',name:',img.name,',state:',img.state

        myKey = sys.argv[1]
        print myKey

        my_security_gp = get_or_make_group(ec2_conn,"ssh")
        if my_security_gp.rules == []: # Group was just now created
            my_security_gp.authorize(src_group=my_security_gp)

        print my_security_gp.name

        zones =ec2_conn.get_all_zones()
        for zone in zones:
            print zone
        zone_val=zones[6]
        print zone_val

    #   create instance 
        reservation=ec2_conn.run_instances(
                       image_id,
                       key_name=myKey,
                       instance_type='m1.small',
                       security_groups=['default'],
    		   placement = 'melbourne-qh2')

        instance = reservation.instances[0]

    #   poll for status
        while instance.state != 'running':
            print 'instance is %s' % instance.state
            time.sleep(5)
            instance.update()
            print "Instance state: %s" % (instance.state)

        print "instance %s done!" % (instance.id)
        print "instance IP is %s" % (instance.ip_address)
        print "You can now SSH into this server with ec2-user@%s" % (instance.ip_address)

        counter += 1
        if counter == 1:
            with open("role_ip","a+") as r:
                r.write('Merge instance:' + instance.ip_address + '\n')
        elif counter <= 5:
            with open("role_ip","a+") as r:
                r.write('Computing instance:' + instance.ip_address + '\n')
        elif counter <= 7:
            with open("role_ip","a+") as r:
                r.write('harvester:' + instance.ip_address + '\n')
        else:
            with open("role_ip","a+") as r:
                r.write('webserver:' + instance.ip_address + '\n')
        with open("just_ip","a+") as f:
            f.write(instance.ip_address + '\n')
       


