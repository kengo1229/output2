<?php
ini_set('log_errors','On');

ini_set('error_log','php.log');

session_start();


$enemyrobots = array();

abstract class  Creature{
  protected $name;
  protected $hp;
  protected $max_hp;
  protected $img;
  protected $min_attack;
  protected $max_attack;

  public function __construct($name, $hp, $max_hp, $img, $min_attack, $max_attack){

    $this->name = $name;
    $this->hp = $hp;
    $this->max_hp = $max_hp;
    $this->img = $img;
    $this->min_attack = $min_attack;
    $this->max_attack = $max_attack;
  }

  public function attack($targetObj){

    $attackPoint = mt_rand($this->min_attack, $this->max_attack);
    $targetObj->setHp($targetObj->getHp() - $attackPoint) ;
    History::set($this->name.'の攻撃！');
    History::set($targetObj->getName().'に'.$attackPoint.'ポイントのダメージを与えた！');
  }

  public function setHp($num){
    $this->hp = filter_var($num, FILTER_VALIDATE_INT);
  }


  public function getHp(){
    return $this->hp ;
  }


  public function getMax_Hp(){
    return $this->max_hp ;
  }

  public function getImg(){
    return $this->img ;
  }

  public function getName(){
    return $this->name ;
  }


}
class Enemyrobot extends Creature{


  public function halfAttack($targetObj){

    $attackPoint = mt_rand($this->min_attack, $this->max_attack);
    History::set($targetObj->name.'は守りを固めた！');
    History::set($this->name.'の攻撃！');
    $targetObj->setHp($targetObj->getHp() - (int)($attackPoint/2)) ;
    History::set((int)($attackPoint/2).'ポイントのダメージを受けた！');

  }

public function avoidAttack($targetObj){
    History::set($targetObj->name.'は攻撃を避けようとしている');

    if(!mt_rand(0,1)){
      History::set($this->getName().'の攻撃！');
      History::set('回避成功！');
      History::set('ノーダメージ！');
    }else{
      $attackPoint = mt_rand($this->min_attack, $this->max_attack);
      History::set($this->name.'の攻撃！');
      History::set('回避失敗！');
      $targetObj->setHp($targetObj->getHp() - $attackPoint) ;
      History::set($attackPoint."ポイントのダメージを受けた！");
    }
}


}

class Dragon extends Enemyrobot{

  private $lastattack;

   function __construct($name, $hp, $max_hp, $img, $min_attack, $max_attack , $lastattack){

   parent::__construct($name, $hp, $max_hp, $img, $min_attack, $max_attack);

    $this->lastattack = $lastattack;
  }

  public function attack($targetObj){
    if(mt_rand(0,4)){
      parent::attack($targetObj);
    }else{
      $attackPoint = $this->lastattack;
      $targetObj->setHp($targetObj->getHp() - $attackPoint);
      History::set($this->name.'の一撃！');
      History::set($attackPoint.'ポイントのダメージを受けた！');
    }
  }

  public function halfAttack($targetObj){

    if(!mt_rand(0,4)){
      $attackPoint = mt_rand($this->min_attack, $this->max_attack);
    }else{
      $attackPoint = $this->lastattack;
    }

    History::set($targetObj->name.'は守りを固めた！');
    History::set($this->name.'の攻撃！');
    $targetObj->setHp($targetObj->getHp() - (int)($attackPoint/2)) ;
    History::set((int)($attackPoint/2).'ポイントのダメージを受けた！');

  }

public function avoidAttack($targetObj){
    History::set($targetObj->name.'は攻撃を避けようとしている');

    if(!mt_rand(0,4)){
      $attackPoint = mt_rand($this->min_attack, $this->max_attack);
    }else{
      $attackPoint = $this->lastattack;
    }

    if(!mt_rand(0,1)){
      History::set($this->getName().'の攻撃！');
      History::set('回避成功！');
      History::set('ノーダメージ！');
    }else{
      $attackPoint = mt_rand($this->min_attack, $this->max_attack);
      History::set($this->name.'の攻撃！');
      History::set('回避失敗！');
      $targetObj->setHp($targetObj->getHp() - $attackPoint) ;
      History::set($attackPoint."ポイントのダメージを受けた！");
    }
}
}

class Human extends Creature{


}

class History{


  public static function set($str){
      if(empty($_SESSION['history'])) $_SESSION['history'] = '';
      $_SESSION['history'] .= $str.'&#010;';
  }

  public static function clear(){
    unset($_SESSION['history']);
  }
}


$enemyrobots[] = new Enemyrobot('敵ロボ01', 300, 300, 'img/enemy01.png', 30, 50 );
$enemyrobots[] = new Enemyrobot('敵ロボ02', 400, 400, 'img/enemy02.png', 20, 60 );
$enemyrobots[] = new Enemyrobot('敵ロボ03', 500, 500, 'img/enemy03.png', 40, 80 );
$enemyrobots[] = new Enemyrobot('敵ロボ04', 600, 600, 'img/enemy04.png', 10, 50 );
$enemyrobots[] = new Enemyrobot('敵ロボ05', 700, 700, 'img/enemy05.png', 30, 60 );
$enemyrobots[] = new Dragon('ドラゴン', 10000, 10000, 'img/dragon.png', 50, 70 , 300 );
$human         = new Human('ロボおじ',1000,1000,'mainrobo.png', 60, 90);

  function createHuman(){
    global $human;

    $_SESSION['human'] = $human;
  }

  function createRobot(){
    global $enemyrobots;

    $enemyrobot = $enemyrobots[mt_rand(0,5)];
    History::set($enemyrobot->getName().'が現れた！');
    $_SESSION['enemyrobot'] = $enemyrobot;
  }

  function init(){
    $_SESSION['knockDownCount'] = 0;
    History::set('ゲームスタート！');
    createHuman();
    createRobot();

  }

  function gameOver(){

    $_SESSION = array();
  }


    // post送信があった場合
    if(!empty($_POST)){
      error_log('post送信アリ');

      $startflg = (!empty($_POST['start'])) ? true : false ;
      $attackflg = (!empty($_POST['attack'])) ? true : false ;
      $defenseflg = (!empty($_POST['defense'])) ? true : false ;
      $avoidflg = (!empty($_POST['avoid'])) ? true : false ;

      if($startflg){

        init();
      }else{
        if($attackflg){

          $_SESSION['human']->attack($_SESSION['enemyrobot']);
          // 敵ロボからの攻撃
          $_SESSION['enemyrobot']->attack($_SESSION['human']);
          // 自分のHPが0になったら、ゲームオーバー関数を読み込む
          if($_SESSION['human']->getHp() <= 0){

             gameOver();

          }else{
            if($_SESSION['enemyrobot']->getHp() <= 0){
              History::clear();
              History::set($_SESSION['enemyrobot']->getName().'を倒した！');
              $_SESSION['knockDownCount'] += 1;
              // 敵のHPが0になったら、新しい敵をだす
              createRobot();
            }
          }

        }elseif($defenseflg){
          $_SESSION['enemyrobot']-> halfAttack($_SESSION['human']);
          if($_SESSION['human']->getHp() <= 0){
             gameOver();
          }
        }elseif($avoidflg){
          $_SESSION['enemyrobot']-> avoidAttack($_SESSION['human']);
          if($_SESSION['human']->getHp() <= 0){
             gameOver();
          }
        }

      }

      $_POST = array();
    }



 ?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>ロボット対戦</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <?php if(empty($_SESSION)){ ?>
      <div id="black-screen">
        <h1>ロボット対戦</h1>
        <form class="" action="" method="post">
          <input type="submit" name="start" value="はじめますか？">
        </form>
      </div>
    <?php }else{?>

    <header id="status">
      <div id="own-status">
        <p><meter value="<?php echo  $_SESSION['human']->getHp(); ?>" optimum="750" low="250" high="500" max="<?php echo $_SESSION['human']->getMax_Hp() ?>" min="0"></meter></p>
        <p>HP:<?php echo  $_SESSION['human']->getHp(); ?>/1000</p>
        <p>倒したモンスター数：<?php echo $_SESSION['knockDownCount']  ?></p>
      </div>
      <div id="enemy-status">
        <p><meter value="<?php echo  $_SESSION['enemyrobot']->getHp();?>" optimum=<?php echo  ($_SESSION['enemyrobot']->getMax_Hp() * 0.8);?> low=<?php echo  ($_SESSION['enemyrobot']->getMax_Hp() * 0.3);?>
           high=<?php echo ($_SESSION['enemyrobot']->getMax_Hp() * 0.5) ;?> max=<?php echo $_SESSION['enemyrobot']->getMax_Hp() ?> min= 0></meter></p>
        <p id="right-float">HP:<?php echo $_SESSION['enemyrobot']->getHp(); ?>/<?php echo $_SESSION['enemyrobot']->getMax_Hp() ; ?></p>
      </div>
    </header>
    <main>
      <div id="own-img">
        <img class="robot-image" src="img/mainrobo.png" alt="">
      </div>
      <div id="enemy-img">
        <img class="robot-image" src="<?php echo $_SESSION['enemyrobot']->getImg() ; ?>" alt="">
      </div>
    </main>
    <footer>
      <form class="" action="" method="post">
        <div id="message-area">
          <textarea name="name" id="js-textbox"><?php echo $_SESSION['history']; ?></textarea>
          <div id="command-area">
            <input type="submit" name="attack" value="攻撃する">
            <input type="submit" name="defense" value="防御する">
            <input type="submit" name="avoid" value="回避する">
          </div>
        </div>
      </form>
    </footer>
    <?php }  ?>
    <script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script src="textbox.js">

</script>
  </body>
</html>
