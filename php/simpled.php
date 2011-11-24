<?php		class sdgroup {		var $sdVersion=1.1;		var $sdFileVersion=2;				var $name;		var $grps=array();		var $props=array();				var $BraceStyle=1; /* 0=none 1=BSD_Allman 2=? */		var $Tab="\t";				function __construct($name){			$this->name = $name;			/*$this->props[0]=new sdprop('PropertyName','Value01');*/		}				function FromStringBase($IsFirst, $Data, &$Index, $AllowEqualsInValue)		{			if ($Data==""){return "Data is empty!";}						$Results="";			$State=0; /*0=Nothing 1=InProperty 2=InComment*/						$StartIndex=$Index;	/*The start of the group.*/			$ErrorIndex=0; /*Used for error handling.*/			$tName=""; /*Temp group or propery name.*/			$tValue=""; /*same but for value.*/						while ($Index < strlen($Data)) {				/*$chr=substr($Data, $Index, 1);*/				$chr=$Data[$Index];								switch ($State){					case 0: /*In nothing*/						switch ($chr){							case "=":								$ErrorIndex=$Index;								$State=1; /*We are now in state 1(in property)*/								break;															case ";":								$tName="";								$tValue="";								$Results .= " #Found end of property but no beginning at index: " . $Index;								break;															case "{": /*New group*/								echo "New group: ".trim($tName);								$Index++;								$tmpG =& new sdgroup(trim($tName));								$Results .= $tmpG->FromStringBase(false, $Data, $Index, $AllowEqualsInValue);								$this->grps[]=& $tmpG;								$tName="";								break;															case "}": /*End of current group*/								if ($IsFirst==true){									$tName .=$chr;								}else{									return $Results;								}								break;															case "/":								if ($Index-1>=0){									if (substr($Data, $Index-1, 1)=="/"){										$tName="";										$State=2;										$ErrorIndex=$Index;									}								}								break;															default:								$tName .= $chr;								break;						}												break;					case 1: /*In property.*/						if ($chr==";"){							$this->props[]= new sdprop(trim($tName),$tValue);							$tName="";							$tValue="";							$State=0;													}elseif( $chr=="="){							if ($AllowEqualsInValue==true){								$tValue .= $chr;							}else{								$Results .= "  #Missing end of property " . trim($tName) . " at index: " . $ErrorIndex;                                $ErrorIndex = $Index;                                $tName = "";                                $tValue = "";							}						}else{							$tValue .=$chr;						}						break;											case 2; /*In comment*/						if ($Index-1>=0){							if (substr($Data,$Index-1,1)=="/"){								$State=0;							}						}						break;				}				$Index++;			}						if ($State==1){				$Results .= " #Missing end of property " . trim($tName) . " at index: " . $ErrorIndex;            }elseif ($State == 2){                $Results .= " #Missing end of comment " . trim($tName) . " at index: " . $ErrorIndex;            }elseif ($IsFirst==false){                $Results .= "  #Missing end of group " . trim($tName) . " at index: " . $StartIndex;            }			return $Results;		} /*end fromstring function.*/			function ToStringBase($IsFirst, $TabCount, $AddVersion, $OverrideStyle)		{			if (!isset($this->grp) and !isset($this->props)){return "";}			if ($TabCount < -1){$TabCount=-2;} /*Tab count Below -1 means use zero tabs.*/						$CurrentStyle=$this->BraceStyle;			If ($OverrideStyle<>0){$CurrentStyle=$OverrideStyle;}						$tmp="";						if($AddVersion==true){				$tmp="SimpleD{Version=".$this->sdVersion.";FormatVersion=".$this->sdFileVersion.";}";			}						/* Name and start of group. Name{ */			if ($IsFirst==false){				switch ($CurrentStyle){					case 0:						$tmp .=$this->name."{";						break;											case 1:						$tmp .=$this->name."\n".$this->GetTabs($TabCount)."{";						break;				}			}						/* Groups and properties */			switch ($CurrentStyle){				Case 0:					if(isset($this->props)){						for ($i = 0; $i <count($this->props); $i++){							$tmp .=$this->props[$i]->name."=".$this->props[$i]->value;						}					}					if(isset($this->grps)){						for ($i = 0; $i <count($this->grps); $i++){							$tmp .=$this->grps[$i]->ToStringBase(false, $TabCount+1, false, $OverrideStyle);						}					}					break;				case 1:					if(isset($this->props)){						for ($i = 0; $i <count($this->props); $i++){							$tmp .="\n".$this->GetTabs($TabCount+1).$this->props[$i]->name."=".$this->props[$i]->value;						}					}					if(isset($this->grps)){						for ($i = 0; $i <count($this->grps); $i++){							$tmp .="\n".$this->GetTabs($TabCount+1).$this->grps[$i]->ToStringBase(false, $TabCount+1, false, $OverrideStyle);						}					}					break;			}									/* End of group } */			if ($IsFirst==false){				switch ($CurrentStyle){					case 0:						$tmp .="\n";						break;										case 1:						$tmp .="\n".$this->GetTabs($TabCount)."}";						break;				}			}						return $tmp;		}				function GetTabs($Count){			if($Count<1){return "";}			return str_repeat($this->Tab,$Count);		}		}		class sdprop {		var $name;		var $value;		function __construct($name, $value){			$this->name=$name;			$this->value=$value;		}	}		/*	$g= new sdgroup("TheName",false);	echo $g->name;	echo $g->props[0]->name;	*/			$g=new sdgroup("");	$index=0;	echo "<p> FromStringBase: ". $g->FromStringBase(true,"gname{p=2;}",$index,true);	if (isset($g->grps[0])){		$g->BraceStyle=0;		echo "<p>".$g->ToStringBase(true, -1, true, 0);	}else{		echo "<p>group 0 is unset.";	}			?>