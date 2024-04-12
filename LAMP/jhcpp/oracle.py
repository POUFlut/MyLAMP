# -*- coding: utf-8 -*-
try:
    
    #import lib
    import sys
    import cx_Oracle
    import json
    import re
    from datetime import datetime
    
    #get parameter(sql command)
    args = sys.argv
    arg1 = args[1]
    
    #query check
    if re.match(r"SELECT.*", arg1):
        #oracle default
        username = ''
        password = ''
        database = ''
        
        #oracle connection
        connection = cx_Oracle.connect(username, password, database, encoding = 'UTF-8')
        cursor = connection.cursor()
        
        #execute oracle command
        cursor.execute(arg1)
        
        #get columns name as array
        column_names = [desc[0] for desc in cursor.description]
        
        #fetch result
        result = cursor.fetchall()
        
        #define datetime default function
        def handle_datetime(obj):
            if isinstance(obj, datetime):
                return obj.strftime('%Y-%m-%d %H:%M:%S')
        
        #check result
        if not result:
            print 0;
        else:
            #format to json type
            json_result = []
            for row in result:
                row_dict = dict(zip(column_names, row))
                json_result.append(row_dict)
            
            #output to string
            json_output = json.dumps(json_result, default=handle_datetime)
            print(json_output)
        
        #close oracle object
        cursor.close()
        connection.close()
        
except cx_Oracle.DatabaseError as e:
    error, = e.args
    print("Error:", error.code)
    print("Error:", error.message)
    