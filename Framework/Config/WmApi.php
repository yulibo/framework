<?php 
/**
 * WmApi 配置文件
  */
  namespace Core\Config;

  class WmApi{

        public $read = [
                "url"  => "http://10.143.62.151/dis/disdo.do",
                        "sign" => "test",
                                "spNumber" => "10655"
                                    ];

                                        public $write = [
                                                "url"  => "http://10.143.62.151/dis/disdo.do",
                                                        "sign" => "test",
                                                                "spNumber" => "10655"
                                                                    ];
                                                                        public $sso = [
                                                                                "url"  => "http://10.143.62.151/sso/accredit.do",
                                                                                        //"url"  => "http://10.213.200.244/Mall/Order/test",
                                                                                                "sign" => "test",
                                                                                                        "spNumber" => "10655"
                                                                                                            ];
                                                                                                                public $ssoOut = [
                                                                                                                        "url"  => "http://10.143.62.151/sso/logout.do",
                                                                                                                                //"url"  => "http://10.213.200.244/Mall/Order/test",
                                                                                                                                        "sign" => "test",
                                                                                                                                                "spNumber" => "10655"
                                                                                                                                                    ];
                                                                                                                                                        

  }
  ?>
